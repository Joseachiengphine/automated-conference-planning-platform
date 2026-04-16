<?php

namespace App\Http\Controllers;

use App\Models\Conference;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Models\User;
use App\Services\ConferenceNotificationService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ConferenceRegistrationController extends Controller
{
    public function create(Conference $conference): View
    {
        $conference->load(['registrationFields' => fn ($query) => $query->orderBy('sort_order')]);

        return view('conferences.register', [
            'conference' => $conference,
            'registrationFields' => $conference->registrationFields,
            'registrationClosed' => $this->registrationClosed($conference),
        ]);
    }

    public function store(Request $request, Conference $conference): RedirectResponse
    {
        $conference->load(['registrationFields' => fn ($query) => $query->orderBy('sort_order')]);
        $registration = null;

        if ($this->registrationClosed($conference)) {
            throw ValidationException::withMessages([
                'conference' => 'Registration for this conference is currently closed.',
            ]);
        }

        if ($conference->registrationFields->isEmpty()) {
            throw ValidationException::withMessages([
                'conference' => 'Registration is not available yet for this conference.',
            ]);
        }

        $validated = Validator::make(
            $request->all(),
            $this->rulesFor($conference),
            [],
            $this->attributesFor($conference),
        )->validate();

        $answers = collect($validated['answers'] ?? []);
        $identity = $this->resolveParticipantIdentity($conference, $answers);

        DB::transaction(function () use ($conference, $answers, $identity, &$registration): void {
            $participant = $this->upsertParticipant($identity);

            $registration = Registration::query()
                ->where('conference_id', $conference->id)
                ->where('participant_id', $participant->id)
                ->first();

            if (! $registration) {
                $registration = Registration::create([
                    'conference_id' => $conference->id,
                    'participant_id' => $participant->id,
                    'registration_code' => $this->generateRegistrationCode(),
                    'status' => 'registered',
                    'confirmed' => true,
                    'confirmed_at' => now(),
                ]);
            }

            foreach ($conference->registrationFields as $field) {
                RegistrationAnswer::updateOrCreate(
                    [
                        'registration_id' => $registration->id,
                        'field_id' => $field->id,
                    ],
                    [
                        'value' => (string) ($answers->get($field->field_key) ?? ''),
                    ],
                );
            }
        });

        app(ConferenceNotificationService::class)->sendRegistrationConfirmation($registration);

        return redirect()->route('conferences.registration.success', [
            'conference' => $conference,
            'registrationCode' => $registration->registration_code,
        ]);
    }

    public function success(Conference $conference, string $registrationCode): View
    {
        $registration = Registration::query()
            ->with(['participant', 'answers.field'])
            ->where('conference_id', $conference->id)
            ->where('registration_code', $registrationCode)
            ->firstOrFail();

        return view('conferences.registration-success', [
            'conference' => $conference,
            'registration' => $registration,
            'qrCodeDataUri' => $this->qrCodeSvg(
                route('conferences.checkin.show', [
                    'conference' => $conference,
                    'registrationCode' => $registration->registration_code,
                ])
            ),
        ]);
    }

    private function rulesFor(Conference $conference): array
    {
        $rules = [];

        foreach ($conference->registrationFields as $field) {
            $baseRules = $field->is_required ? ['required'] : ['nullable'];

            $rules["answers.{$field->field_key}"] = match ($field->field_type) {
                'email' => [...$baseRules, 'email', 'max:255'],
                'number' => [...$baseRules, 'numeric'],
                'date' => [...$baseRules, 'date'],
                'select' => [...$baseRules, Rule::in($field->options_json ?? [])],
                default => [...$baseRules, 'string', 'max:255'],
            };
        }

        return $rules;
    }

    private function attributesFor(Conference $conference): array
    {
        return $conference->registrationFields
            ->mapWithKeys(fn ($field) => ["answers.{$field->field_key}" => $field->label])
            ->all();
    }

    private function resolveParticipantIdentity(Conference $conference, Collection $answers): array
    {
        $nameFieldKey = $this->bestMatchingFieldKey(
            $conference,
            ['full_name', 'name', 'participant_name', 'attendee_name'],
            ['full name', 'name', 'participant name', 'attendee name'],
            excludedKeywords: ['organization', 'company', 'business', 'institution', 'employer'],
        );
        $emailFieldKey = $this->bestMatchingFieldKey(
            $conference,
            ['email_address', 'email', 'participant_email', 'attendee_email'],
            ['email', 'email address', 'participant email', 'attendee email'],
            preferredTypes: ['email'],
        );
        $phoneFieldKey = $this->bestMatchingFieldKey(
            $conference,
            ['phone', 'phone_number', 'mobile_number', 'contact_number'],
            ['phone', 'phone number', 'mobile', 'mobile number', 'contact number'],
        );

        $name = filled($nameFieldKey) ? $this->firstMatchingAnswer($answers, [$nameFieldKey]) : null;
        $email = filled($emailFieldKey) ? $this->firstMatchingAnswer($answers, [$emailFieldKey]) : null;
        $phone = filled($phoneFieldKey) ? $this->firstMatchingAnswer($answers, [$phoneFieldKey]) : null;

        $messages = [];

        if (blank($name)) {
            $messages[$nameFieldKey ? "answers.{$nameFieldKey}" : 'identity.name'] = $nameFieldKey
                ? 'Fill in the attendee name before submitting.'
                : 'Add a name question to the registration form before collecting registrations.';
        }

        if (blank($email)) {
            $messages[$emailFieldKey ? "answers.{$emailFieldKey}" : 'identity.email'] = $emailFieldKey
                ? 'Fill in the attendee email before submitting.'
                : 'Add an email question to the registration form before collecting registrations.';
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }

        return [
            'name' => $name,
            'email' => Str::lower($email),
            'phone' => $phone,
        ];
    }

    private function firstMatchingAnswer($answers, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $answers->get($key);

            if (filled($value)) {
                return trim((string) $value);
            }
        }

        return null;
    }

    private function bestMatchingFieldKey(
        Conference $conference,
        array $preferredKeys,
        array $labelKeywords,
        array $preferredTypes = [],
        array $excludedKeywords = [],
    ): ?string {
        $match = $conference->registrationFields
            ->map(function ($field) use ($preferredKeys, $labelKeywords, $preferredTypes, $excludedKeywords): array {
                return [
                    'field_key' => $field->field_key,
                    'score' => $this->scoreFieldMatch(
                        $field->field_key,
                        $field->label,
                        $field->field_type,
                        $preferredKeys,
                        $labelKeywords,
                        $preferredTypes,
                        $excludedKeywords,
                    ),
                ];
            })
            ->filter(fn (array $candidate): bool => $candidate['score'] > 0)
            ->sortByDesc('score')
            ->first();

        return $match['field_key'] ?? null;
    }

    private function scoreFieldMatch(
        string $fieldKey,
        string $label,
        string $fieldType,
        array $preferredKeys,
        array $labelKeywords,
        array $preferredTypes,
        array $excludedKeywords,
    ): int {
        $normalizedKey = Str::of($fieldKey)->lower()->replace('-', '_')->toString();
        $normalizedLabel = Str::of($label)->lower()->squish()->toString();
        $score = 0;

        foreach ($preferredKeys as $preferredKey) {
            $normalizedPreferredKey = Str::of($preferredKey)->lower()->replace('-', '_')->toString();

            if ($normalizedKey === $normalizedPreferredKey) {
                $score += 100;
                continue;
            }

            if (str_contains($normalizedKey, $normalizedPreferredKey)) {
                $score += 60;
            }
        }

        foreach ($labelKeywords as $keyword) {
            $normalizedKeyword = Str::of($keyword)->lower()->squish()->toString();
            $keywordKey = Str::of($keyword)->lower()->replace(' ', '_')->toString();

            if ($normalizedLabel === $normalizedKeyword || $normalizedKey === $keywordKey) {
                $score += 90;
                continue;
            }

            if (str_contains($normalizedLabel, $normalizedKeyword) || str_contains($normalizedKey, $keywordKey)) {
                $score += 35;
            }
        }

        if (in_array($fieldType, $preferredTypes, true)) {
            $score += 50;
        }

        foreach ($excludedKeywords as $keyword) {
            $normalizedKeyword = Str::of($keyword)->lower()->squish()->toString();
            $keywordKey = Str::of($keyword)->lower()->replace(' ', '_')->toString();

            if (str_contains($normalizedLabel, $normalizedKeyword) || str_contains($normalizedKey, $keywordKey)) {
                $score -= 120;
            }
        }

        return max($score, 0);
    }

    private function upsertParticipant(array $identity): User
    {
        $participant = User::firstOrNew(['email' => $identity['email']]);

        $participant->name = $identity['name'];
        $participant->phone = $identity['phone'];

        if (! $participant->exists) {
            $participant->role = 'participant';
            $participant->password = Str::password(16);
            $participant->email_verified_at = now();
        }

        $participant->save();

        return $participant;
    }

    private function generateRegistrationCode(): string
    {
        do {
            $code = 'SUM-' . Str::upper(Str::random(8));
        } while (Registration::where('registration_code', $code)->exists());

        return $code;
    }

    private function registrationClosed(Conference $conference): bool
    {
        return $conference->status !== 'published'
            || ($conference->registration_deadline && $conference->registration_deadline->isPast());
    }

    private function qrCodeSvg(string $payload): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_M,
            'scale' => 6,
            'addQuietzone' => true,
        ]);

        return (new QRCode($options))->render($payload);
    }
}
