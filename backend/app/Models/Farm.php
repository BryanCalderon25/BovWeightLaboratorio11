<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Farm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nombre',
        'ubicacion',
        'descripcion',
        'area_hectareas',
    ];

    /**
     * Obtener el usuario dueño de la finca.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener los animales de la finca.
     */
    public function animals()
    {
        return $this->hasMany(Animal::class);
    }
}
