<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->helperText('Optional contact number for conference coordination.'),
                Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'speaker' => 'Speaker',
                        'participant' => 'Participant',
                    ])
                    ->required()
                    ->default('participant')
                    ->helperText('This controls the platform role stored on the user record.'),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Access roles')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Optional permission roles managed through Filament Shield.'),
                DateTimePicker::make('email_verified_at')
                    ->label('Email verified at')
                    ->helperText('Leave blank if the user has not verified their email yet.'),
                TextInput::make('password')
                    ->password()
                    ->autocomplete('new-password')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->rule(Password::default())
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->same('passwordConfirmation')
                    ->helperText('Set a password when creating a user, or leave blank to keep the current password.'),
                TextInput::make('passwordConfirmation')
                    ->label('Confirm password')
                    ->password()
                    ->autocomplete('new-password')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false),
            ]);
    }
}
