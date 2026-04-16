<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for {{ $conference->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f4c81;
            --accent: #2cb1a6;
            --muted: #64748b;
            --border: #dbe4ee;
            --soft: #f8fbff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(44, 177, 166, 0.12), transparent 24%),
                linear-gradient(180deg, #f8fbff 0%, #eef5fb 100%);
            color: #132238;
        }

        .page-shell {
            min-height: 100vh;
            padding: 48px 0;
        }

        .register-card,
        .info-card {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(15, 76, 129, 0.08);
            border-radius: 28px;
            box-shadow: 0 22px 60px rgba(15, 76, 129, 0.10);
        }

        .register-card {
            padding: 34px;
        }

        .info-card {
            padding: 28px;
            overflow: hidden;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(44, 177, 166, 0.12);
            color: var(--accent);
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 14px;
        }

        .page-copy {
            color: var(--muted);
            line-height: 1.8;
            margin-bottom: 24px;
        }

        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 14px;
            border-radius: 16px;
            background: var(--soft);
            border: 1px solid var(--border);
            color: #1e293b;
            font-weight: 600;
            margin: 0 10px 12px 0;
        }

        .form-label {
            font-weight: 700;
        }

        .form-control,
        .form-select {
            border-radius: 14px;
            border-color: var(--border);
            padding: 14px 16px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(15, 76, 129, 0.45);
            box-shadow: 0 0 0 0.25rem rgba(15, 76, 129, 0.12);
        }

        .form-note {
            color: var(--muted);
            font-size: 0.93rem;
            margin-top: 8px;
        }

        .btn-register {
            background: var(--primary);
            color: #fff;
            border: 0;
            border-radius: 16px;
            padding: 14px 22px;
            font-weight: 700;
        }

        .btn-register:hover {
            background: #0c3d67;
            color: #fff;
        }

        .btn-ghost {
            border: 2px solid rgba(15, 76, 129, 0.18);
            color: var(--primary);
            border-radius: 16px;
            padding: 14px 22px;
            font-weight: 700;
            background: #fff;
        }

        .side-visual {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            min-height: 320px;
            margin-bottom: 22px;
        }

        .side-visual img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .side-visual::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15, 76, 129, 0.14) 0%, rgba(15, 76, 129, 0.78) 100%);
        }

        .side-visual-copy {
            position: absolute;
            left: 22px;
            right: 22px;
            bottom: 22px;
            z-index: 2;
            color: #fff;
        }

        .side-visual-copy h3 {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .side-visual-copy p {
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.86);
            line-height: 1.7;
        }

        .mini-step {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px 0;
            border-bottom: 1px solid rgba(15, 76, 129, 0.08);
        }

        .mini-step:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .mini-step-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(44, 177, 166, 0.12);
            color: var(--accent);
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .mini-step h6 {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .mini-step p {
            margin-bottom: 0;
            color: var(--muted);
            line-height: 1.7;
        }

        @media (max-width: 991px) {
            .page-title {
                font-size: 2rem;
            }

            .register-card,
            .info-card {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
<div class="page-shell">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="register-card">
                    <div class="eyebrow">
                        <i class="bi bi-ticket-perforated-fill"></i>
                        <span>Reserve your spot</span>
                    </div>

                    <h1 class="page-title">Register for {{ $conference->title }}</h1>
                    <p class="page-copy">
                        Fill in your details, get your registration code, and keep your QR ready for check-in on event day.
                    </p>

                    <div class="mb-3">
                        <div class="meta-chip"><i class="bi bi-geo-alt-fill text-primary"></i>{{ $conference->venue }}</div>
                        <div class="meta-chip"><i class="bi bi-calendar-event-fill text-primary"></i>{{ $conference->start_datetime?->format('M d, Y h:i A') }}</div>
                        <div class="meta-chip"><i class="bi bi-hourglass-split text-primary"></i>Closes {{ $conference->registration_deadline?->format('M d, Y') }}</div>
                    </div>

                    @php
                        $visibleErrorKeys = $registrationFields
                            ->map(fn ($field) => 'answers.' . $field->field_key)
                            ->all();
                        $summaryErrors = collect($errors->getMessages())
                            ->filter(fn ($messages, $key) => ! in_array($key, $visibleErrorKeys, true))
                            ->flatten()
                            ->unique()
                            ->values();
                    @endphp

                    @if ($errors->any())
                        <div class="alert alert-danger rounded-4 border-0 mb-4">
                            <strong>Please fix the highlighted fields and try again.</strong>
                            @if($summaryErrors->isNotEmpty())
                                <ul class="mb-0 mt-2 ps-3">
                                    @foreach($summaryErrors as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif

                    @if ($registrationClosed)
                        <div class="alert alert-warning rounded-4 border-0 mb-0">
                            Registration for this conference is currently closed.
                        </div>
                    @elseif($registrationFields->isEmpty())
                        <div class="alert alert-secondary rounded-4 border-0 mb-0">
                            Registration opens soon. The organizer has not published the registration questions yet.
                        </div>
                    @else
                        <form method="POST" action="{{ route('conferences.register.store', $conference) }}">
                            @csrf

                            <div class="row g-4">
                                @foreach($registrationFields as $field)
                                    <div class="{{ $field->field_type === 'select' ? 'col-12' : 'col-md-6' }}">
                                        <label class="form-label" for="field_{{ $field->field_key }}">
                                            {{ $field->label }}@if($field->is_required)<span class="text-danger">*</span>@endif
                                        </label>

                                        @if($field->field_type === 'select')
                                            <select
                                                class="form-select @error('answers.' . $field->field_key) is-invalid @enderror"
                                                id="field_{{ $field->field_key }}"
                                                name="answers[{{ $field->field_key }}]"
                                            >
                                                <option value="">Choose an option</option>
                                                @foreach($field->options_json ?? [] as $option)
                                                    <option value="{{ $option }}" @selected(old('answers.' . $field->field_key) == $option)>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input
                                                class="form-control @error('answers.' . $field->field_key) is-invalid @enderror"
                                                id="field_{{ $field->field_key }}"
                                                name="answers[{{ $field->field_key }}]"
                                                type="{{ match($field->field_type) {
                                                    'email' => 'email',
                                                    'number' => 'number',
                                                    'date' => 'date',
                                                    default => 'text',
                                                } }}"
                                                value="{{ old('answers.' . $field->field_key) }}"
                                            >
                                        @endif

                                        @error('answers.' . $field->field_key)
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror

                                        @if($field->field_key === 'email_address')
                                            <div class="form-note">We will use this to identify your registration.</div>
                                        @elseif($field->field_key === 'full_name')
                                            <div class="form-note">Use the name you want on your event registration.</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex flex-column flex-md-row gap-3 mt-4">
                                <button type="submit" class="btn btn-register">Complete Registration</button>
                                <a href="{{ route('home') }}" class="btn btn-ghost">Back to Home</a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="col-lg-5">
                <div class="info-card">
                    <div class="side-visual">
                        <img src="{{ asset('images/hero_section.jpg') }}" alt="Conference atmosphere">
                        <div class="side-visual-copy">
                            <h3>Show up ready, not stressed</h3>
                            <p>Your registration code and QR make event-day check-in feel fast and smooth.</p>
                        </div>
                    </div>

                    <div class="mini-step">
                        <div class="mini-step-icon"><i class="bi bi-ui-checks-grid"></i></div>
                        <div>
                            <h6>Event form so we know about you</h6>
                        </div>
                    </div>

                    <div class="mini-step">
                        <div class="mini-step-icon"><i class="bi bi-qr-code"></i></div>
                        <div>
                            <h6>Instant QR confirmation</h6>
                        </div>
                    </div>

                    <div class="mini-step">
                        <div class="mini-step-icon"><i class="bi bi-bell-fill"></i></div>
                        <div>
                            <h6>Stay in the loop</h6>
                            <p>Use your conference page to keep up with schedule updates, speakers, and announcements.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
