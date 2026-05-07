<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model {
    
    protected $table = 'modulos';
    protected $primaryKey = 'id_modulo';
    public $timestamps = false;

    protected $fillable = [
        'id_modulo',
        'ciclo',
        'modulo',
        'color',
        'idioma',
        'clave_matriculacion',
        'id_profesor',
    ];

    // Relaciones
    
    public function profesor() {
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }

    public function alumnos() {
        return $this->belongsToMany(Alumno::class, 'modulos_alumnos', 'id_modulo', 'id_alumno');
    }

    public function preguntas() {
        return $this->hasMany(Pregunta::class, 'id_modulo');
    }

    public function tests() {
        return $this->hasMany(Test::class, 'id_modulo');
    }

    public function puntuaciones() {
        return $this->hasManyThrough(Puntuacion::class, Test::class, 'id_modulo', 'id_test', 'id_modulo', 'id_test' );
    }
}
