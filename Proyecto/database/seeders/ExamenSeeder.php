<?php

namespace Database\Seeders;

use App\Models\Examen;
use App\Models\Test;
use Illuminate\Database\Seeder;

class ExamenSeeder extends Seeder
{
    public function run(): void
    {
        $tests = Test::where('tipo', 'examen')->get();

        foreach ($tests as $test) {
            Examen::create([
                'id_test'        => $test->id_test,
                'duracion'       => 60,
                'fecha_apertura' => now()->startOfMinute()->subMinutes(10),
                'fecha_cierre'   => now()->startOfMinute()->addMinutes(50),
            ]);
        }
    }
}