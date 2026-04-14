<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|null|\UnitEnum $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Users';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canAccess(): bool
    {
        return static::currentUserCanManageUsers() || parent::canAccess();
    }

    public static function canViewAny(): bool
    {
        return static::currentUserCanManageUsers() || parent::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::currentUserCanManageUsers() || parent::canCreate();
    }

    public static function canEdit(Model $record): bool
    {
        return static::currentUserCanManageUsers() || parent::canEdit($record);
    }

    public static function canDelete(Model $record): bool
    {
        return static::currentUserCanManageUsers() || parent::canDelete($record);
    }

    public static function canDeleteAny(): bool
    {
        return static::currentUserCanManageUsers() || parent::canDeleteAny();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    protected static function currentUserCanManageUsers(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        // Keep user management visible to application admins while Shield
        // permissions are still being introduced and seeded.
        return $user->role === 'admin'
            || $user->hasRole(config('filament-shield.super_admin.name'));
    }
}
