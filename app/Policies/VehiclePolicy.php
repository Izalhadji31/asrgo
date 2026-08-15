<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->role === 'admin' || ($user->role === 'mitra' && $vehicle->mitra_id === $user->id);
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $this->view($user, $vehicle);
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $this->view($user, $vehicle);
    }

    public function approve(User $user, Vehicle $vehicle): bool
    {
        return $user->role === 'admin';
    }

    public function assignDriver(User $user, Vehicle $vehicle): bool
    {
        return $user->role === 'admin';
    }
}
