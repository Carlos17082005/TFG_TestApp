<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/login');

        $middleware->alias([
            'profesor' => \App\Http\Middleware\EsProfesor::class,
            'alumno'   => \App\Http\Middleware\EsAlumno::class,
            'moduloProfesor'   => \App\Http\Middleware\ModuloPerteneceProfesor::class,
            'moduloAlumno' => \App\Http\Middleware\ModuloPerteneceAlumno::class,
            'alumnoExamen' => \App\Http\Middleware\AlumnoTieneAccesoExamen::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
