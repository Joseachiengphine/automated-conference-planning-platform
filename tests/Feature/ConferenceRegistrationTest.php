<?php

namespace Tests\Feature;

use App\Models\Conference;
use App\Models\ConferenceRegistrationField;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConferenceRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_accepts_builder_generated_name_and_email_fields(): void
    {
        $creator = User::factory()->create([
            'role' => 'admin',
        ]);

        $conference = Conference::create([
            'title' => 'Builder Test Event',
            'description' => 'Testing public registration.',
            'venue' => 'Nairobi',
            'start_datetime' => now()->addWeek(),
            'end_datetime' => now()->addWeek()->addHours(4),
            'registration_deadline' => now()->addDays(5),
            'status' => 'published',
            'created_by' => $creator->id,
        ]);

        ConferenceRegistrationField::create([
            'conference_id' => $conference->id,
            'field_key' => 'your_full_name',
            'label' => 'Your full name',
            'field_type' => 'text',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        ConferenceRegistrationField::create([
            'conference_id' => $conference->id,
            'field_key' => 'best_email_to_reach_you',
            'label' => 'Best email to reach you',
            'field_type' => 'email',
            'is_required' => true,
            'sort_order' => 2,
        ]);

        ConferenceRegistrationField::create([
            'conference_id' => $conference->id,
            'field_key' => 'age',
            'label' => 'Age',
            'field_type' => 'number',
            'is_required' => false,
            'sort_order' => 3,
        ]);

        $response = $this->post(route('conferences.register.store', $conference), [
            'answers' => [
                'your_full_name' => 'Casey Builder',
                'best_email_to_reach_you' => 'CASEY@example.com',
                'age' => '25',
            ],
        ]);

        $registration = Registration::first();

        $response->assertRedirect(route('conferences.registration.success', [
            'conference' => $conference,
            'registrationCode' => $registration?->registration_code,
        ]));

        $this->assertDatabaseHas('users', [
            'name' => 'Casey Builder',
            'email' => 'casey@example.com',
        ]);

        $this->assertDatabaseHas('registrations', [
            'conference_id' => $conference->id,
            'status' => 'registered',
        ]);

        $this->assertSame(3, RegistrationAnswer::count());
    }

    public function test_registration_surfaces_missing_identity_fields_when_form_builder_omits_them(): void
    {
        $creator = User::factory()->create([
            'role' => 'admin',
        ]);

        $conference = Conference::create([
            'title' => 'Age Only Event',
            'description' => 'Testing missing identity fields.',
            'venue' => 'Nairobi',
            'start_datetime' => now()->addWeek(),
            'end_datetime' => now()->addWeek()->addHours(4),
            'registration_deadline' => now()->addDays(5),
            'status' => 'published',
            'created_by' => $creator->id,
        ]);

        ConferenceRegistrationField::create([
            'conference_id' => $conference->id,
            'field_key' => 'age',
            'label' => 'Age',
            'field_type' => 'number',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $response = $this
            ->from(route('conferences.register', $conference))
            ->post(route('conferences.register.store', $conference), [
                'answers' => [
                    'age' => '25',
                ],
            ]);

        $response
            ->assertRedirect(route('conferences.register', $conference))
            ->assertSessionHasErrors([
                'identity.name',
                'identity.email',
            ]);
    }
}
