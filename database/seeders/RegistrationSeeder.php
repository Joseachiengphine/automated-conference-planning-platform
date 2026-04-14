<?php

namespace Database\Seeders;

use App\Models\Checkin;
use App\Models\Conference;
use App\Models\NotificationLog;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Models\User;
use Illuminate\Database\Seeder;

class RegistrationSeeder extends Seeder
{
    private const JOHN_EMAIL = 'john.kibuna@example.com';
    private const PARTICIPANT_TARGET = 300;

    public function run(): void
    {
        $johnKibuna = User::where('email', self::JOHN_EMAIL)->firstOrFail();
        $conferences = Conference::with('registrationFields')->orderBy('id')->get();
        $participants = $this->participants();
        $participantUsers = $this->participantUsers($participants);

        if ($conferences->isEmpty()) {
            return;
        }

        $participantsPerConference = (int) ceil(count($participants) / $conferences->count());
        $conferenceParticipantGroups = array_chunk($participants, $participantsPerConference);

        foreach ($conferences as $conferenceIndex => $conference) {
            $conferenceParticipants = $conferenceParticipantGroups[$conferenceIndex] ?? [];

            foreach ($conferenceParticipants as $participantIndex => $participantData) {
                /** @var \App\Models\User $participant */
                $participant = $participantUsers[$participantData['email']];
                $confirmed = (($participantIndex + $conferenceIndex + 1) % 5) !== 0;

                $registration = Registration::firstOrCreate(
                    [
                        'conference_id' => $conference->id,
                        'participant_id' => $participant->id,
                    ],
                    [
                        'registration_code' => sprintf('CONF-%02d-P%03d', $conference->id, $participantIndex + 1),
                    ],
                );

                $registration->fill(
                    [
                        'status' => 'registered',
                        'confirmed' => $confirmed,
                        'confirmed_at' => $confirmed
                            ? $conference->start_datetime->copy()->subDays(3)->addHours($participantIndex)
                            : null,
                    ],
                );
                $registration->save();

                $this->seedRegistrationAnswers($conference, $registration, $participant, $participantData);
                $this->seedCheckin($conference, $registration, $johnKibuna->id, $participantIndex, $confirmed);
                $this->seedNotificationLogs($conference, $registration);
            }
        }
    }

    private function participants(): array
    {
        $firstNames = [
            'Akinyi', 'Brian', 'Catherine', 'David', 'Faith',
            'Kevin', 'Lucy', 'Martin', 'Naomi', 'Oscar',
            'Purity', 'Quincy', 'Rose', 'Samuel', 'Tina',
            'Victor', 'Winnie', 'Yvonne', 'Zack', 'Mercy',
        ];

        $lastNames = [
            'Odhiambo', 'Njoroge', 'Wanjiku', 'Kiptoo', 'Atieno',
            'Mutua', 'Kamau', 'Otieno', 'Achieng', 'Mwangi',
            'Kariuki', 'Jepkorir', 'Nduta', 'Omondi', 'Cheruiyot',
        ];

        $counties = ['Nairobi', 'Kiambu', 'Mombasa', 'Kisumu', 'Nakuru', 'Uasin Gishu'];

        $organizations = [
            'LakeTech Events',
            'Nairobi Dev Community',
            'Afya Digital Hub',
            'Rift Valley Innovations',
            'Coastline Events',
            'Savannah Systems',
            'SummitOps Africa',
            'Karibu Conferences',
            'Eastern Region Labs',
            'Metro Event Tech',
        ];

        $participants = [];
        $lastNameCount = count($lastNames);

        for ($index = 0; $index < self::PARTICIPANT_TARGET; $index++) {
            $participantNumber = $index + 1;
            $firstName = $firstNames[intdiv($index, $lastNameCount)];
            $lastName = $lastNames[$index % $lastNameCount];

            $participants[] = $this->participant(
                "{$firstName} {$lastName}",
                strtolower(sprintf('%s.%s@gmail.com', $firstName, $lastName)),
                sprintf('+2547%08d', 11000000 + $participantNumber),
                $counties[$index % count($counties)],
                $organizations[$index % count($organizations)],
            );
        }

        return $participants;
    }

    private function participantUsers(array $participants)
    {
        return collect($participants)->mapWithKeys(function (array $participantData) {
            $user = User::updateOrCreate(
                ['phone' => $participantData['phone']],
                [
                    'name' => $participantData['name'],
                    'email' => $participantData['email'],
                    'role' => 'participant',
                    'email_verified_at' => now(),
                    'password' => 'password',
                ],
            );

            return [$participantData['email'] => $user];
        });
    }

    private function seedRegistrationAnswers(
        Conference $conference,
        Registration $registration,
        User $participant,
        array $participantData,
    ): void {
        $answerMap = [
            'full_name' => $participant->name,
            'email_address' => $participant->email,
            'county' => $participantData['county'],
            'organization' => $participantData['organization'],
        ];

        foreach ($conference->registrationFields as $field) {
            RegistrationAnswer::updateOrCreate(
                [
                    'registration_id' => $registration->id,
                    'field_id' => $field->id,
                ],
                [
                    'value' => $answerMap[$field->field_key] ?? 'N/A',
                ],
            );
        }
    }

    private function seedCheckin(
        Conference $conference,
        Registration $registration,
        int $checkedInBy,
        int $participantIndex,
        bool $confirmed,
    ): void {
        if (! $confirmed) {
            return;
        }

        Checkin::updateOrCreate(
            ['registration_id' => $registration->id],
            [
                'conference_id' => $conference->id,
                'checked_in_at' => $conference->start_datetime->copy()->addMinutes(20 + ($participantIndex * 10)),
                'checked_in_by' => $checkedInBy,
                'checkin_method' => 'qr',
            ],
        );
    }

    private function seedNotificationLogs(Conference $conference, Registration $registration): void
    {
        foreach ($this->notificationSubjects() as $subjectIndex => $subject) {
            NotificationLog::updateOrCreate(
                [
                    'registration_id' => $registration->id,
                    'subject' => $subject,
                ],
                [
                    'conference_id' => $conference->id,
                    'channel' => 'email',
                    'sent_at' => $registration->created_at->copy()->addHours($subjectIndex + 1),
                    'status' => 'sent',
                ],
            );
        }
    }

    private function notificationSubjects(): array
    {
        return [
            'Registration confirmation from John Kibuna',
            'Event reminder from John Kibuna',
        ];
    }

    private function participant(
        string $name,
        string $email,
        string $phone,
        string $county,
        string $organization,
    ): array {
        return [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'county' => $county,
            'organization' => $organization,
        ];
    }
}
