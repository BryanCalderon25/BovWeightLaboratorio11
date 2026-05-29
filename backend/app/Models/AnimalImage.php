<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'ruta_imagen',
        'tipo',
    ];

    /**
     * Obtener el animal al que pertenece la imagen.
     */
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
