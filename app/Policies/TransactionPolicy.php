<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('client');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // Les admins peuvent voir toutes les transactions
        if ($user->hasRole('admin')) {
            return true;
        }

        // Les clients ne peuvent voir que leurs propres transactions
        if ($user->hasRole('client') && $user->client) {
            return $transaction->compte->client_id === $user->client->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('client');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        // Seuls les admins peuvent modifier les transactions
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        // Seuls les admins peuvent supprimer les transactions
        return $user->hasRole('admin');
    }
}
