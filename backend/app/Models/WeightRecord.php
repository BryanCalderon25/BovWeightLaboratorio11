<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'peso_estimado',
        'fecha_pesaje',
        'animal_image_id',
        'datos_modelo',
        'estado_sincronizacion',
        'notas',
    ];

    protected $casts = [
        'datos_modelo' => 'array',
        'fecha_pesaje' => 'date',
    ];

    /**
     * Obtener el animal al que pertenece el registro de pesaje.
     */
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    /**
     * Obtener la imagen asociada a este pesaje (si existe).
     */
    public function image()
    {
        return $this->belongsTo(AnimalImage::class, 'animal_image_id');
    }
}
