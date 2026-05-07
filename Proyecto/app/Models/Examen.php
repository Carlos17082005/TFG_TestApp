<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Examen extends Model
{
    protected $table = 'examenes';
    protected $primaryKey = 'id_examen';
    public $timestamps = false;

    protected $casts = [
        'fecha_apertura' => 'datetime',
    ];

    protected $fillable = [
        'id_examen',
        'duracion',
        'fecha_apertura',
        'id_test',
    ];

    public function test() {
        return $this->belongsTo(Test::class, 'id_test', 'id_test');
    }
}
