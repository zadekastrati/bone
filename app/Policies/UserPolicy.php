<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function view(User $actor, User $model): bool
    {
        return $actor->isAdmin();
    }

    public function create(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function update(User $actor, User $model): bool
    {
        return $actor->isAdmin();
    }

    public function delete(User $actor, User $model): bool
    {
        return $actor->isAdmin() && $actor->id !== $model->id;
    }
}
