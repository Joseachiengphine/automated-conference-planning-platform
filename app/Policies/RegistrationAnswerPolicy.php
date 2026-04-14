<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RegistrationAnswer;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegistrationAnswerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RegistrationAnswer');
    }

    public function view(AuthUser $authUser, RegistrationAnswer $registrationAnswer): bool
    {
        return $authUser->can('View:RegistrationAnswer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RegistrationAnswer');
    }

    public function update(AuthUser $authUser, RegistrationAnswer $registrationAnswer): bool
    {
        return $authUser->can('Update:RegistrationAnswer');
    }

    public function delete(AuthUser $authUser, RegistrationAnswer $registrationAnswer): bool
    {
        return $authUser->can('Delete:RegistrationAnswer');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RegistrationAnswer');
    }

    public function restore(AuthUser $authUser, RegistrationAnswer $registrationAnswer): bool
    {
        return $authUser->can('Restore:RegistrationAnswer');
    }

    public function forceDelete(AuthUser $authUser, RegistrationAnswer $registrationAnswer): bool
    {
        return $authUser->can('ForceDelete:RegistrationAnswer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RegistrationAnswer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RegistrationAnswer');
    }

    public function replicate(AuthUser $authUser, RegistrationAnswer $registrationAnswer): bool
    {
        return $authUser->can('Replicate:RegistrationAnswer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RegistrationAnswer');
    }

}