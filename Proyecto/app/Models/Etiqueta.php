<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etiqueta extends Model
{
    protected $table = 'etiquetas';
    protected $primaryKey = 'id_etiqueta';
    public $timestamps = false;

    protected $fillable = ['nombre', 'id_modulo'];

    public function modulo() {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    public function preguntas() {
        return $this->belongsToMany(Pregunta::class, 'etiqueta_pregunta', 'id_etiqueta', 'id_pregunta');
    }
}