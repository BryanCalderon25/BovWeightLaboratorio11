<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'role',
        'token',
        'expires_at',
        'max_uses',
        'uses_count',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Obtener la finca asociada.
     */
    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * Verificar si la invitación es válida.
     */
    public function isValid()
    {
        if ($this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        return true;
    }
}
