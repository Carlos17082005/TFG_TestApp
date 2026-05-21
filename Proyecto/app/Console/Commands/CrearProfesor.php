<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CrearProfesor extends Command
{
    /**
     * El nombre y firma del comando.
     */
    protected $signature = 'profesor:crear 
                            {--nombre= : El nombre del profesor} 
                            {--apellidos= : Los apellidos del profesor} 
                            {--email= : El correo electrónico} 
                            {--password= : La contraseña}';

    /**
     * La descripción del comando.
     */
    protected $description = 'Crea un nuevo usuario con rol de profesor y su registro en la tabla profesores';

    /**
     * Ejecuta la lógica del comando.
     */
    public function handle()
    {
        // 1. Recoger los datos (si no se pasan por comando, se preguntan en la consola)
        $nombre = $this->option('nombre') ?? $this->ask('¿Cuál es el nombre del profesor?');
        $apellidos = $this->option('apellidos') ?? $this->ask('¿Cuáles son los apellidos?');
        $email = $this->option('email') ?? $this->ask('¿Cuál es el email?');
        $password = $this->option('password') ?? $this->secret('¿Cuál es la contraseña?');

        // 2. Validar que el email no exista ya
        if (DB::table('usuarios')->where('email', $email)->exists()) {
            $this->error("El email {$email} ya está registrado.");
            return 1;
        }

        try {
            // 3. DB::transaction asegura que si falla la tabla profesores, no se guarde el usuario a medias
            DB::transaction(function () use ($nombre, $apellidos, $email, $password) {
                
                // Insertar en usuarios y obtener el ID
                $idUsuario = DB::table('usuarios')->insertGetId([
                    'nombre'    => $nombre,
                    'apellidos' => $apellidos,
                    'email'     => $email,
                    'password'  => Hash::make($password), // Encriptar siempre
                    'rol'       => 'profesor',
                ]);

                // Insertar en profesores usando el ID del usuario
                DB::table('profesores')->insert([
                    'id_profesor' => $idUsuario,
                ]);
            });

            $this->info("¡Éxito! El profesor {$nombre} ha sido creado correctamente.");
            return 0;

        } catch (\Exception $e) {
            $this->error("Hubo un error al crear el profesor: " . $e->getMessage());
            return 1;
        }
    }
}