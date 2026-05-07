<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $table = 'tests';
    protected $primaryKey = 'id_test';
    public $timestamps = false;

    protected $fillable = [
        'id_test',
        'nombre',
        'descripcion',
        'tipo',
        'id_modulo',
    ];

    public function modulo() {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    public function preguntas() {
        return $this->belongsToMany(Pregunta::class, 'preguntas_tests', 'id_test', 'id_pregunta');
    }

    public function puntuaciones() {
        return $this->hasMany(Puntuacion::class, 'id_test');
    }

    public function examen() {
    return $this->hasOne(Examen::class, 'id_test', 'id_test');
}
}
