<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasApiSecurity
{
    /**
     * Check if the current user can access this resource
     */
    public function canAccess(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Admin can access everything
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user owns this resource
        if (method_exists($this, 'user_id') && $this->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if the current user can modify this resource
     */
    public function canModify(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Admin can modify everything
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user owns this resource
        if (method_exists($this, 'user_id') && $this->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Get the owner of this resource
     */
    public function getOwner()
    {
        if (method_exists($this, 'user')) {
            return $this->user;
        }

        if (property_exists($this, 'user_id')) {
            return \App\Models\User::find($this->user_id);
        }

        return null;
    }
}