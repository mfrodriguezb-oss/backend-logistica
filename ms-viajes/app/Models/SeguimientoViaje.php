<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoViaje extends Model
{
    protected $table = 'seguimientos_viajes';
    
    protected $fillable = [
        'programacion_viaje_id',
        'fecha',
        'hora',
        'estado',
        'novedad'
    ];
}