<?php

namespace Database\Seeders;

use App\Models\Etiqueta;
use App\Models\Modulo;
use Illuminate\Database\Seeder;

class EtiquetaSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = Modulo::all()->keyBy('modulo');

        $etiquetasPorModulo = [
            'Programación' => [
                'variables', 'funciones', 'poo', 'arrays', 'bucles',
            ],
            'Bases de Datos' => [
                'sql', 'joins', 'normalizacion',
            ],
            'Desarrollo Web' => [
                'html', 'css', 'javascript',
            ],
            'Sistemas Operativos' => [
                'linux', 'comandos',
            ],
            'Entornos de Desarrollo' => [
                'git', 'ide',
            ],
        ];

        foreach ($etiquetasPorModulo as $nombreModulo => $etiquetas) {
            $modulo = $modulos[$nombreModulo] ?? null;

            if (!$modulo) {
                continue;
            }

            foreach ($etiquetas as $nombre) {
                Etiqueta::create([
                    'id_modulo' => $modulo->id_modulo,
                    'nombre'    => $nombre,
                ]);
            }
        }
    }
}