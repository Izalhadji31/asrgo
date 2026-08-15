<?php

namespace App\Policies;

use App\Models\Payout;
use App\Models\User;

class PayoutPolicy
{
    public function update(User $user, Payout $payout): bool
    {
        return $user->role === 'admin';
    }
}
