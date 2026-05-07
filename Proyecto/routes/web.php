<?php

use App\Http\Controllers\AlumnoModuloController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\ProfesorModuloController;
use App\Http\Controllers\PreguntaController;
use App\Http\Controllers\ProfesorAlumnoController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\RealizarTestController;
use App\Http\Controllers\AlumnoTestController;
use App\Models\Modulo;
use Illuminate\Support\Facades\Route;

Route::get('/', [InicioController::class, 'indexMostrar'])->name('inicio.index.mostrar');

// =================
// ===== GUEST =====
// =================

Route::middleware('guest')->controller(InicioController::class)->group(function(){

    // Mostrar el login
    Route::get('/login', 'loginMostrar')->name('inicio.login.mostrar');

    // Mostrar el register
    Route::get('/register', 'registerMostrar')->name('inicio.register.mostrar');

    Route::controller(AuthController::class)->group(function() {
        // Iniciar sesion
        Route::post('/login', 'login')->name('auth.login');

        // Registrarse
        Route::post('/register', 'register')->name('auth.register');
    });
});

// ================
// ===== AUTH =====
// ================

Route::middleware('auth')->controller(AuthController::class)->group(function() {

    // ==================
    // ==== PROFESOR ====
    // ==================

    Route::middleware('profesor')->prefix('profesor')->group(function() {

        // =================
        // ==== MODULOS ====
        // =================

        Route::controller(ProfesorModuloController::class)->group(function() {
            // Mostrar formulario para crear modulos nuevos
            Route::get('/modulos/create', 'create')->name('profesor.modulos.create');
            // Crear el modulo
            Route::post('/modulos', 'store')->name('profesor.modulos.store');

            Route::middleware('moduloProfesor')->group(function () {
                // Mostrar formulario para editar módulo
                Route::get('/modulos/{modulo}/edit', 'edit')->name('profesor.modulos.edit');
                // Editar módulo
                Route::put('/modulos/{modulo}', 'update')->name('profesor.modulos.update');
                // Eliminar módulo
                Route::delete('/modulos/{modulo}', 'destroy')->name('profesor.modulos.destroy');
            });
        });

        Route::middleware('moduloProfesor')->controller(ProfesorModuloController::class)->group(function () {
            Route::get('/dashboard/{modulo?}', [InicioController::class, 'dashboardProfesorMostrar'])->name('inicio.dashboardProfesor.mostrar');
                
            // ===================
            // ==== PREGUNTAS ====
            // ===================

            Route::controller(PreguntaController::class)->group(function() {
                // Página de preguntas
                Route::get('/{modulo}/preguntas', 'index')->name('profesor.preguntas.index');

                // Mostrar formulario para crear preguntas nuevas
                Route::get('/{modulo}/preguntas/create', 'create')->name('profesor.preguntas.create');

                // Crear pregunta
                Route::post('/{modulo}/preguntas', 'store')->name('profesor.preguntas.store');

                // Obtener pregunta para editar
                Route::get('/{modulo}/preguntas/{pregunta}/edit', 'edit')->name('profesor.preguntas.edit');

                // Guardar pregunta actualizada
                Route::put('/{modulo}/preguntas/{pregunta}', 'update')->name('profesor.preguntas.update');

                // Eliminar pregunta
                Route::delete('/{modulo}/preguntas/{pregunta}', 'destroy')->name('profesor.preguntas.destroy');
            });

            // ===============
            // ==== TESTS ====
            // ===============

            Route::controller(TestController::class)->group(function() {
                // Página de tests
                Route::get('/{modulo}/tests', 'index')->name('profesor.tests.index');

                // Mostrar formulario para crear tests nuevos
                Route::get('/{modulo}/tests/create', 'create')->name('profesor.tests.create');

                // Crear test
                Route::post('/{modulo}/tests', 'store')->name('profesor.tests.store');


                /////////////////////////////////////////////////////////////////////
                // Guardar borrador del test en sesión antes de ir a preguntas
                // crear test borrador
                Route::post('/{modulo}/tests/borrador/nuevo', 'borrador')->name('profesor.tests.borrador.nuevo');


                // Mostrar formulario para editar tests
                Route::get('/{modulo}/tests/{test}/edit', 'edit')->name('profesor.tests.edit');

                // Editar test
                Route::put('/{modulo}/tests/{test}', 'update')->name('profesor.tests.update');

                // Eliminar test
                Route::delete('/{modulo}/tests/{test}', 'destroy')->name('profesor.tests.destroy');
            

                /////////////////////////////////////////////////////////////////////
                // Guardar borrador del test en sesión antes de ir a preguntas
                // editar test borrador
                Route::post('/{modulo}/tests/{test}/borrador', 'borrador')->name('profesor.tests.borrador');
            });

            // =====================
            // ==== HACER TESTS ====
            // =====================
            Route::controller(RealizarTestController::class)->group(function() {
                Route::get('/modulo/{modulo}/test/{test}/iniciar', 'iniciarTest')->name('profesor.tests.iniciar');
                Route::get('/modulo/{modulo}/test/{test}/realizar', 'probarTest')->name('profesor.tests.realizar');
                Route::post('/modulo/{modulo}/test/{test}/realizar', 'correccionTest')->name('profesor.tests.corregir');
            });

            // =====================================
            // ==== GESTION PROFESOR -> ALUMNOS ====
            // =====================================

            Route::controller(ProfesorAlumnoController::class)->group(function() {
                // Mostrar los alumnos pertenecientes al módulo 
                Route::get('/{modulo}/alumnos', 'index')->name('profesor.alumnos.index');

                // Actualizar el acceso de los alumnos al modulo
                Route::put('/{modulo}/alumnos', 'update')->name('profesor.alumnos.update');

                // Eliminar alumnos del módulo
                Route::delete('/{modulo}/alumnos/{alumno}', 'destroy')->name('profesor.alumnos.destroy');


                /////////////////////////////////////////////////////////////////////////////////////
                // Historial
                Route::get('/{modulo}/historial', 'historial')->name('profesor.historial');
            });

        });

        
    });

    // ================
    // ==== ALUMNO ====
    // ================
    Route::middleware('alumno')->prefix('alumno')->group(function() {

        // Acceder Modulos

        Route::middleware('moduloAlumno')->controller(AlumnoModuloController::class)->group(function() {

            // Dashboard de alumno
            Route::get('/dashboard/{modulo?}', [InicioController::class, 'dashboardAlumnoMostrar'])->name('inicio.dashboardAlumno.mostrar');

            ///////////////////////////////////////////////////////////////////////////////////////////////////////
            // test
            Route::get('/{modulo}/examen', [AlumnoTestController::class, 'examenes'])->name('alumno.tests.examen');
            Route::get('/{modulo}/practica', [AlumnoTestController::class, 'practicas'])->name('alumno.tests.practica');


            /////////////////////////////////////////////////////////////////////////////////////
            // Historial
            Route::get('/{modulo}/historial', 'historial')->name('alumno.historial');

            /////////////////////////////////////////////////////////////////////////////////////
            // Ajustes

            Route::get('/{modulo}/ajustes', 'ajustes')->name('alumno.ajustes');
            Route::delete('/{modulo}/ajustes/abandonar', 'abandonar')->name('alumno.ajustes.abandonar');
            
            // ===================
            // === HACER TESTS ===
            // ===================
            Route::controller(RealizarTestController::class)->group(function() {
                Route::get('/modulo/{modulo}/test/{test}/iniciar', 'iniciarTest')->name('alumno.tests.iniciar');
                Route::get('/modulo/{modulo}/test/{test}/realizar', 'probarTest')->name('alumno.tests.realizar')->middleware('alumnoExamen');
                Route::post('/modulo/{modulo}/test/{test}/realizar', 'correccionTest')->name('alumno.tests.corregir');
            });
        });

        Route::controller(AlumnoModuloController::class)->group(function() {
            // Unirse a módulos nuevos
            Route::get('/matriculas', 'index')->name('alumno.matriculas.index');
            Route::get('/matriculas/{modulo}', 'create')->name('alumno.matriculas.create');
            Route::post('/matriculas/{modulo}', 'store')->name('alumno.matriculas.store');
        });
    });

    // Cerrar sesión

    Route::get('/logout', 'logout')->name('auth.logout');
    Route::post('/logout', 'logout')->name('auth.logout');
});