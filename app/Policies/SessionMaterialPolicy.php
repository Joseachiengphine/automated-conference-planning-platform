<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SessionMaterial;
use Illuminate\Auth\Access\HandlesAuthorization;

class SessionMaterialPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SessionMaterial');
    }

    public function view(AuthUser $authUser, SessionMaterial $sessionMaterial): bool
    {
        return $authUser->can('View:SessionMaterial');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SessionMaterial');
    }

    public function update(AuthUser $authUser, SessionMaterial $sessionMaterial): bool
    {
        return $authUser->can('Update:SessionMaterial');
    }

    public function delete(AuthUser $authUser, SessionMaterial $sessionMaterial): bool
    {
        return $authUser->can('Delete:SessionMaterial');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SessionMaterial');
    }

    public function restore(AuthUser $authUser, SessionMaterial $sessionMaterial): bool
    {
        return $authUser->can('Restore:SessionMaterial');
    }

    public function forceDelete(AuthUser $authUser, SessionMaterial $sessionMaterial): bool
    {
        return $authUser->can('ForceDelete:SessionMaterial');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SessionMaterial');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SessionMaterial');
    }

    public function replicate(AuthUser $authUser, SessionMaterial $sessionMaterial): bool
    {
        return $authUser->can('Replicate:SessionMaterial');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SessionMaterial');
    }

}