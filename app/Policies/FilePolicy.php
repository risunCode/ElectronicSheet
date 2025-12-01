<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, File $file): bool
    {
        return $user->isAdmin() || $file->owner_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canCreateDocuments();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, File $file): bool
    {
        if ($user->isGuest()) {
            return false;
        }
        
        return $user->isAdmin() || $file->owner_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, File $file): bool
    {
        if ($user->isGuest()) {
            return false;
        }
        
        return $user->isAdmin() || $file->owner_id === $user->id;
    }

    /**
     * Determine whether the user can download the file.
     */
    public function download(User $user, File $file): bool
    {
        return $user->isAdmin() || $file->owner_id === $user->id;
    }
}
