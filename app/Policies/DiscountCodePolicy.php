<?php

namespace App\Policies;

use App\Models\User;

class DiscountCodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
