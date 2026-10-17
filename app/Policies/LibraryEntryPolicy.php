<?php

namespace App\Policies;

use App\Models\LibraryEntry;
use App\Models\User;

class LibraryEntryPolicy
{
    public function update(User $user, LibraryEntry $entry): bool
    {
        return $entry->user_id === $user->id;
    }

    public function delete(User $user, LibraryEntry $entry): bool
    {
        return $entry->user_id === $user->id;
    }
}
