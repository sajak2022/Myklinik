<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Pasien;
use Illuminate\Auth\Access\HandlesAuthorization;

class PasienPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Pasien');
    }

    public function view(AuthUser $authUser, Pasien $pasien): bool
    {
        return $authUser->can('View:Pasien');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Pasien');
    }

    public function update(AuthUser $authUser, Pasien $pasien): bool
    {
        return $authUser->can('Update:Pasien');
    }

    public function delete(AuthUser $authUser, Pasien $pasien): bool
    {
        return $authUser->can('Delete:Pasien');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Pasien');
    }

    public function restore(AuthUser $authUser, Pasien $pasien): bool
    {
        return $authUser->can('Restore:Pasien');
    }

    public function forceDelete(AuthUser $authUser, Pasien $pasien): bool
    {
        return $authUser->can('ForceDelete:Pasien');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Pasien');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Pasien');
    }

    public function replicate(AuthUser $authUser, Pasien $pasien): bool
    {
        return $authUser->can('Replicate:Pasien');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Pasien');
    }

}