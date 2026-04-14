<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ConferenceRegistrationField;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConferenceRegistrationFieldPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ConferenceRegistrationField');
    }

    public function view(AuthUser $authUser, ConferenceRegistrationField $conferenceRegistrationField): bool
    {
        return $authUser->can('View:ConferenceRegistrationField');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ConferenceRegistrationField');
    }

    public function update(AuthUser $authUser, ConferenceRegistrationField $conferenceRegistrationField): bool
    {
        return $authUser->can('Update:ConferenceRegistrationField');
    }

    public function delete(AuthUser $authUser, ConferenceRegistrationField $conferenceRegistrationField): bool
    {
        return $authUser->can('Delete:ConferenceRegistrationField');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ConferenceRegistrationField');
    }

    public function restore(AuthUser $authUser, ConferenceRegistrationField $conferenceRegistrationField): bool
    {
        return $authUser->can('Restore:ConferenceRegistrationField');
    }

    public function forceDelete(AuthUser $authUser, ConferenceRegistrationField $conferenceRegistrationField): bool
    {
        return $authUser->can('ForceDelete:ConferenceRegistrationField');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ConferenceRegistrationField');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ConferenceRegistrationField');
    }

    public function replicate(AuthUser $authUser, ConferenceRegistrationField $conferenceRegistrationField): bool
    {
        return $authUser->can('Replicate:ConferenceRegistrationField');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ConferenceRegistrationField');
    }

}