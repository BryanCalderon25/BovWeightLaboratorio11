<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'farm_id',
        'arete',
        'nombre',
        'raza',
        'fecha_nacimiento',
        'genero',
        'proposito',
        'peso_actual',
        'notas',
    ];

    /**
     * Obtener la finca a la que pertenece el animal.
     */
    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * Obtener las imágenes del animal.
     */
    public function images()
    {
        return $this->hasMany(AnimalImage::class);
    }

    /**
     * Obtener el historial de pesaje del animal.
     */
    public function weightRecords()
    {
        return $this->hasMany(WeightRecord::class);
    }
}
