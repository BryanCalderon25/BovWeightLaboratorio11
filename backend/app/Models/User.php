<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'invited_farm_id', 'guest_expires_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasRoles, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'guest_expires_at' => 'datetime',
        ];
    }

    /**
     * Obtener las fincas del usuario.
     */
    public function farms()
    {
        return $this->hasMany(Farm::class);
    }

    /**
     * Verificar si el usuario tiene acceso compartido (invitado) a una finca.
     */
    public function hasSharedAccess($farmId)
    {
        if ($this->invited_farm_id !== (int)$farmId) {
            return false;
        }

        if ($this->guest_expires_at && now()->gt($this->guest_expires_at)) {
            return false;
        }

        return true;
    }
}
