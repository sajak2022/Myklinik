<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Pendaftaran;
use Illuminate\Auth\Access\HandlesAuthorization;

class PendaftaranPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Pendaftaran')
            || $authUser->hasRole(['super_admin', 'Admin', 'Dokter', 'Perawat', 'Bidan'])
            || ($authUser->pegawai && in_array($authUser->pegawai->profesi, ['Dokter', 'Perawat', 'Bidan']));
    }

    public function view(AuthUser $authUser, Pendaftaran $pendaftaran): bool
    {
        return $authUser->can('View:Pendaftaran')
            || $authUser->hasRole(['super_admin', 'Admin', 'Dokter', 'Perawat', 'Bidan'])
            || ($authUser->pegawai && in_array($authUser->pegawai->profesi, ['Dokter', 'Perawat', 'Bidan']));
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Pendaftaran');
    }

    public function update(AuthUser $authUser, Pendaftaran $pendaftaran): bool
    {
        return $authUser->can('Update:Pendaftaran');
    }

    public function delete(AuthUser $authUser, Pendaftaran $pendaftaran): bool
    {
        return $authUser->can('Delete:Pendaftaran');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Pendaftaran');
    }

    public function restore(AuthUser $authUser, Pendaftaran $pendaftaran): bool
    {
        return $authUser->can('Restore:Pendaftaran');
    }

    public function forceDelete(AuthUser $authUser, Pendaftaran $pendaftaran): bool
    {
        return $authUser->can('ForceDelete:Pendaftaran');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Pendaftaran');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Pendaftaran');
    }

    public function replicate(AuthUser $authUser, Pendaftaran $pendaftaran): bool
    {
        return $authUser->can('Replicate:Pendaftaran');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Pendaftaran');
    }

}
