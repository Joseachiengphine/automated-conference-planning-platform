<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Conference;
use App\Models\ConferenceRegistrationField;
use App\Models\Session;
use App\Models\SessionMaterial;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ConferenceSeeder extends Seeder
{
    private const JOHN_NAME = 'John Kibuna';
    private const JOHN_EMAIL = 'john.kibuna@example.com';
    private const JOHN_MODERATOR_EMAIL = 'john.kibuna.moderator@example.com';
    private const JOHN_PHONE = '+254700123456';
    private const SPEAKER_TARGET = 50;

    public function run(): void
    {
        $johnKibuna = User::updateOrCreate(
            ['email' => self::JOHN_EMAIL],
            [
                'name' => self::JOHN_NAME,
                'role' => 'admin',
                'phone' => self::JOHN_PHONE,
                'email_verified_at' => now(),
                'password' => 'password',
            ],
        );

        $conferenceBlueprints = $this->conferenceBlueprints();
        $existingSpeakerCount = collect($conferenceBlueprints)->sum(fn (array $conferenceData): int => count($conferenceData['speakers']));
        $generatedSpeakerCount = max(self::SPEAKER_TARGET - $existingSpeakerCount, 0);
        $generatedSpeakerGroups = $generatedSpeakerCount > 0
            ? array_chunk(
                $this->generatedSpeakers($generatedSpeakerCount),
                (int) ceil($generatedSpeakerCount / count($conferenceBlueprints)),
            )
            : [];

        foreach ($conferenceBlueprints as $conferenceIndex => $conferenceData) {
            $conference = Conference::updateOrCreate(
                ['title' => $conferenceData['title']],
                [
                    'description' => $conferenceData['description'],
                    'venue' => $conferenceData['venue'],
                    'start_datetime' => $conferenceData['start_datetime'],
                    'end_datetime' => $conferenceData['end_datetime'],
                    'registration_deadline' => $conferenceData['registration_deadline'],
                    'status' => $conferenceData['status'],
                    'created_by' => $johnKibuna->id,
                ],
            );

            $this->seedRegistrationFields($conference);
            $speakers = array_merge(
                $conferenceData['speakers'],
                $generatedSpeakerGroups[$conferenceIndex] ?? [],
            );
            $speakersByEmail = $this->seedSpeakers($conference, $speakers);
            $this->seedSessions($conference, $conferenceData['sessions'], $speakersByEmail);
            $this->seedAnnouncements($conference, $conferenceData['announcements'], $johnKibuna->id);
        }
    }

    private function conferenceBlueprints(): array
    {
        $john = self::JOHN_NAME;

        return [
            [
                'title' => 'Kenya AI Leadership Summit 2026',
                'description' => "A national summit curated by {$john} to connect AI builders, policy teams, and conference organizers across Kenya.",
                'venue' => 'Kenyatta International Convention Centre, Nairobi',
                'start_datetime' => now()->addDays(14)->setTime(8, 30),
                'end_datetime' => now()->addDays(15)->setTime(17, 30),
                'registration_deadline' => now()->addDays(10)->setTime(18, 0),
                'status' => 'published',
                'speakers' => [
                    $this->speaker(self::JOHN_EMAIL, $john, "{$john} is leading the summit program and guiding practical AI adoption for conference operations in Kenya."),
                    $this->speaker(self::JOHN_MODERATOR_EMAIL, "{$john} - Moderator", "{$john} moderates the strategy conversations and keeps the day flowing for speakers and attendees."),
                ],
                'sessions' => [
                    $this->session(
                        "Opening Keynote with {$john}",
                        "{$john} outlines how conference teams can use AI responsibly to improve planning, registration, and attendee communication.",
                        self::JOHN_EMAIL,
                        now()->addDays(14)->setTime(9, 0),
                        now()->addDays(14)->setTime(10, 0),
                        1,
                        [$this->material('john-kibuna-opening-keynote', 'pdf')],
                        'https://meet.google.com/ken-ai-keynote'
                    ),
                    $this->session(
                        "{$john} Registration Workflow Lab",
                        "A hands-on session where {$john} demonstrates better registration forms, check-ins, and notification flows.",
                        self::JOHN_MODERATOR_EMAIL,
                        now()->addDays(14)->setTime(11, 30),
                        now()->addDays(14)->setTime(12, 30),
                        2,
                        [$this->material('john-kibuna-registration-lab', 'pptx')]
                    ),
                ],
                'announcements' => [
                    $this->announcement("Welcome from {$john}", "{$john} welcomes all attendees and asks everyone to confirm their registration details before arrival.", 'general'),
                    $this->announcement("Session Reminder from {$john}", "{$john} reminds participants that the registration workflow lab starts at 11:30 AM in the main breakout hall.", 'schedule_change'),
                ],
            ],
            [
                'title' => 'Digital Events Operations Forum 2026',
                'description' => "{$john} brings together conference admins and operations teams to improve digital event delivery for Kenyan organizations.",
                'venue' => 'Safari Park Hotel, Nairobi',
                'start_datetime' => now()->addDays(35)->setTime(9, 0),
                'end_datetime' => now()->addDays(36)->setTime(16, 30),
                'registration_deadline' => now()->addDays(30)->setTime(18, 0),
                'status' => 'published',
                'speakers' => [
                    $this->speaker(self::JOHN_EMAIL, $john, "{$john} shares practical lessons from running modern event operations and speaker coordination."),
                ],
                'sessions' => [
                    $this->session(
                        "{$john} on Speaker Operations",
                        "{$john} walks through speaker scheduling, session preparation, and material handover for smooth conference delivery.",
                        self::JOHN_EMAIL,
                        now()->addDays(35)->setTime(10, 0),
                        now()->addDays(35)->setTime(11, 0),
                        1,
                        [$this->material('john-kibuna-speaker-ops', 'pdf')],
                        'https://teams.microsoft.com/l/meetup-join/kenya-events-forum'
                    ),
                    $this->session(
                        "Closing Remarks by {$john}",
                        "{$john} closes the forum with a checklist for registration, attendee updates, and post-event follow-up.",
                        self::JOHN_EMAIL,
                        now()->addDays(36)->setTime(15, 0),
                        now()->addDays(36)->setTime(16, 0),
                        2,
                        [$this->material('john-kibuna-closing-remarks', 'docx')]
                    ),
                ],
                'announcements' => [
                    $this->announcement("Forum Check-in by {$john}", "{$john} asks all registered participants to keep their QR code ready for a faster check-in experience.", 'general'),
                    $this->announcement("{$john} Delay Notice", "{$john} has moved the afternoon networking block by 15 minutes to allow the previous session to finish well.", 'delay'),
                ],
            ],
        ];
    }

    private function registrationFields(): array
    {
        return [
            [
                'field_key' => 'full_name',
                'label' => 'Full Name',
                'field_type' => 'text',
                'is_required' => true,
                'options_json' => null,
                'sort_order' => 1,
            ],
            [
                'field_key' => 'email_address',
                'label' => 'Email Address',
                'field_type' => 'email',
                'is_required' => true,
                'options_json' => null,
                'sort_order' => 2,
            ],
            [
                'field_key' => 'county',
                'label' => 'County',
                'field_type' => 'select',
                'is_required' => true,
                'options_json' => ['Nairobi', 'Kiambu', 'Mombasa', 'Kisumu', 'Nakuru', 'Uasin Gishu'],
                'sort_order' => 3,
            ],
            [
                'field_key' => 'organization',
                'label' => 'Organization',
                'field_type' => 'text',
                'is_required' => false,
                'options_json' => null,
                'sort_order' => 4,
            ],
        ];
    }

    private function generatedSpeakers(int $count): array
    {
        $firstNames = [
            'Alice', 'Ben', 'Caroline', 'Dennis', 'Esther',
            'Felix', 'Grace', 'Hassan', 'Irene', 'James',
            'Lydia', 'Michael', 'Nadia', 'Patrick', 'Queenie',
            'Robert', 'Stella', 'Tony', 'Valerie', 'Willis',
            'Xavier', 'Yvette', 'Zawadi', 'Brenda', 'Collins',
        ];

        $lastNames = [
            'Mwangi', 'Otieno', 'Kariuki', 'Wanjala', 'Mutiso',
            'Achieng', 'Kiprono', 'Njeri', 'Wekesa', 'Omollo',
        ];

        $themes = [
            'AI adoption for event operations',
            'speaker readiness and coordination',
            'digital registration design',
            'hybrid conference facilitation',
            'attendee engagement strategy',
            'event communications and follow-up',
            'operations analytics for conferences',
            'community-driven program design',
        ];

        $organizations = [
            'EventOps Kenya',
            'SummitWorks Africa',
            'Digital Stage Collective',
            'Conference Lab East Africa',
            'Nairobi Innovation Forum',
        ];

        $speakers = [];
        $lastNameCount = count($lastNames);

        for ($index = 0; $index < $count; $index++) {
            $speakerNumber = $index + 1;
            $firstName = $firstNames[intdiv($index, $lastNameCount)];
            $lastName = $lastNames[$index % $lastNameCount];
            $theme = $themes[$index % count($themes)];
            $organization = $organizations[$index % count($organizations)];
            $name = "{$firstName} {$lastName}";

            $speakers[] = $this->speaker(
                strtolower(sprintf('%s.%s.speaker.%03d@example.com', $firstName, $lastName, $speakerNumber)),
                $name,
                "{$name} leads {$theme} at {$organization}, bringing practical experience from large-scale events across Kenya.",
            );
        }

        return $speakers;
    }

    private function seedRegistrationFields(Conference $conference): void
    {
        foreach ($this->registrationFields() as $fieldData) {
            ConferenceRegistrationField::updateOrCreate(
                [
                    'conference_id' => $conference->id,
                    'field_key' => $fieldData['field_key'],
                ],
                [
                    'label' => $fieldData['label'],
                    'field_type' => $fieldData['field_type'],
                    'is_required' => $fieldData['is_required'],
                    'options_json' => $fieldData['options_json'],
                    'sort_order' => $fieldData['sort_order'],
                ],
            );
        }
    }

    private function seedSpeakers(Conference $conference, array $speakers): array
    {
        $speakersByEmail = [];

        foreach ($speakers as $speakerData) {
            $speaker = Speaker::updateOrCreate(
                [
                    'conference_id' => $conference->id,
                    'email' => $speakerData['email'],
                ],
                [
                    'name' => $speakerData['name'],
                    'bio' => $speakerData['bio'],
                ],
            );

            $speakersByEmail[$speaker->email] = $speaker;
        }

        return $speakersByEmail;
    }

    private function seedSessions(Conference $conference, array $sessions, array $speakersByEmail): void
    {
        foreach ($sessions as $sessionData) {
            $session = Session::updateOrCreate(
                [
                    'conference_id' => $conference->id,
                    'title' => $sessionData['title'],
                ],
                [
                    'description' => $sessionData['description'],
                    'online_link' => $sessionData['online_link'],
                    'speaker_id' => $speakersByEmail[$sessionData['speaker_email']]->id ?? null,
                    'start_time' => $sessionData['start_time'],
                    'end_time' => $sessionData['end_time'],
                    'session_order' => $sessionData['session_order'],
                    'status' => 'scheduled',
                ],
            );

            foreach ($sessionData['materials'] as $materialData) {
                SessionMaterial::updateOrCreate(
                    [
                        'session_id' => $session->id,
                        'file_name' => $materialData['file_name'],
                    ],
                    [
                        'file_path' => $materialData['file_path'],
                        'file_type' => $materialData['file_type'],
                        'uploaded_at' => $session->start_time->copy()->subDays(2),
                    ],
                );
            }
        }
    }

    private function seedAnnouncements(Conference $conference, array $announcements, int $createdBy): void
    {
        foreach ($announcements as $announcementData) {
            Announcement::updateOrCreate(
                [
                    'conference_id' => $conference->id,
                    'title' => $announcementData['title'],
                ],
                [
                    'message' => $announcementData['message'],
                    'type' => $announcementData['type'],
                    'created_by' => $createdBy,
                ],
            );
        }
    }

    private function speaker(string $email, string $name, string $bio): array
    {
        return [
            'email' => $email,
            'name' => $name,
            'bio' => $bio,
        ];
    }

    private function session(
        string $title,
        string $description,
        string $speakerEmail,
        Carbon $startTime,
        Carbon $endTime,
        int $sessionOrder,
        array $materials,
        ?string $onlineLink = null,
    ): array {
        return [
            'title' => $title,
            'description' => $description,
            'speaker_email' => $speakerEmail,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'session_order' => $sessionOrder,
            'materials' => $materials,
            'online_link' => $onlineLink,
        ];
    }

    private function material(string $slug, string $extension): array
    {
        return [
            'file_name' => "{$slug}.{$extension}",
            'file_path' => "seeded-materials/{$slug}.{$extension}",
            'file_type' => $extension,
        ];
    }

    private function announcement(string $title, string $message, string $type): array
    {
        return [
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ];
    }
}
