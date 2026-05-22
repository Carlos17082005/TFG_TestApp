<div align="center">

<br/>

# 🎓 TestApp

**Plataforma web educativa para la creación y evaluación de tests formativos en FP**

Proyecto Intermodular · 2ºDAW · Curso 2025/2026

<br/>

![Laravel](https://img.shields.io/badge/Laravel_12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP_8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

<br/>

</div>

---
<div align="center">

## 👥 Equipo

| Nombre |
|--------|
| Álvaro Claudio de las Mozas |
| Carlos Gabriel García Guzmán |
| Miguel Ángel Durán Soto |
</div>

---

## 📋 ¿Qué es TestApp?

TestApp conecta profesores y alumnos dentro de **módulos formativos**. El profesor diseña el banco de preguntas, crea tests de práctica o exámenes con fechas y control de tiempo, y gestiona qué alumnos tienen acceso. El alumno se matricula con una clave, realiza las pruebas disponibles y consulta su historial de resultados.

**Lo que hace diferente a TestApp:**

- **5 tipos de pregunta** adaptados a distintas materias: opción múltiple, verdadero/falso, respuesta de texto, conectar columnas y balance contable.
- **Audio adjunto** en cualquier pregunta como apoyo al enunciado.
- **Exámenes con control total**: fecha de apertura, fecha de cierre, duración con temporizador, un único intento por alumno y envío automático al acabar el tiempo.
- **Corrección automática** con nota sobre 10 y feedback visual por pregunta (configurable).
- **Correo de bienvenida asíncrono** mediante cola de jobs de Laravel.
- **Color de módulo personalizable** que se propaga como variable CSS a toda la interfaz del módulo.


---

## 🧑‍🏫 Rol: Profesor

<details>
<summary><b>📝 Gestión de Preguntas</b></summary>
<br/>

- Creación de preguntas de distintos tipos: **tipo test**, **relacionar**, **selección múltiple**, entre otros.
- Categorización por contenidos: RA's, unidades, segmentación, etc.
- Clasificación por dificultad: 🟢 **Fácil** · 🟡 **Intermedio** · 🔴 **Difícil**

</details>

<details>
<summary><b>📊 Gestión de Tests</b></summary>
<br/>

- Creación de **tests de práctica** y **tests de evaluación**.
- Selección de preguntas de forma **manual** o **automática** con filtros de aleatoriedad:
  - Número de preguntas, categoría y distribución por dificultad.
  - Opción de generar un test **único por alumno**, seleccionando preguntas distintas dentro de los mismos filtros.

</details>

<details>
<summary><b>📈 Seguimiento de Resultados</b></summary>
<br/>

- Acceso al historial de todos los tests finalizados por los alumnos.
- Consulta de puntuaciones individuales y seguimiento del progreso.

</details>

---

## 🧑‍🎓 Rol: Alumno

<details>
<summary><b>✏️ Realización de Tests</b></summary>
<br/>

- Listado de pruebas disponibles, diferenciando claramente **tests de práctica** y **tests de evaluación**.
- Interacción con los distintos tipos de preguntas diseñados por el profesor.
- En tests configurados automáticamente, el alumno recibe un cuestionario **único y personalizado**, respetando la dificultad y los contenidos establecidos.

</details>

<details>
<summary><b>📉 Consulta de Resultados</b></summary>
<br/>

- Historial de tests finalizados con sus puntuaciones.
- Revisión detallada de cada entrega: preguntas **acertadas** ✅ y **falladas** ❌.

</details>

---

## 📸 Capturas de pantalla

> Añade las capturas en la carpeta `docs/screenshots/` y actualiza las rutas.

| Vista | |
|-------|-|
| Dashboard del profesor | ![Dashboard profesor](https://github.com/user-attachments/assets/d8557ed2-c4ac-40b4-a2b6-260e4fa554fa) |
| Dashboard del alumno | ![Dashboard alumno](https://github.com/user-attachments/assets/c104b3ae-0d06-4224-9be1-453520dbfefe) |
| Formulario de pregunta | ![Crear pregunta](https://github.com/user-attachments/assets/ff20eb1a-a01c-4c9b-a849-d0ff34c79173) |
| Realización de test | ![Realizar test](https://github.com/user-attachments/assets/3c8be1e8-4685-43e5-900a-00217c32a7ad) |
| Resultado y corrección | ![Corrección](https://github.com/user-attachments/assets/a4e40beb-e9d2-4d7e-9309-09cdc1c451a3) |
| Historial de puntuaciones | ![Historial](https://github.com/user-attachments/assets/b91e20a3-c2a2-468b-9bc2-3e01d8947262) |

---

## 🏗️ Arquitectura

La aplicación sigue el patrón **MVC de Laravel** con una capa adicional de servicios que separa la lógica de negocio compleja de los controladores.

```
Rutas (web.php)
    └── Middleware (rol + pertenencia al módulo + acceso a examen)
            └── Controladores
                    └── Servicios (PreguntaService · TestService · InicioService)
                            └── Modelos Eloquent
                                    └── Base de datos MySQL
```

### Middleware propio

| Middleware | Función |
|------------|---------|
| `EsProfesor` | Restringe rutas al rol profesor |
| `EsAlumno` | Restringe rutas al rol alumno |
| `ModuloPerteneceProfesor` | Verifica que el módulo pertenece al profesor autenticado |
| `ModuloPerteneceAlumno` | Verifica que el alumno está matriculado y tiene acceso activo |
| `AlumnoTieneAccesoExamen` | Comprueba que el examen está en periodo de apertura y que el alumno no lo ha realizado ya |

### Servicios

| Servicio | Responsabilidad |
|----------|----------------|
| `PreguntaService` | Creación y actualización de preguntas: construcción del JSON por tipo, gestión de archivos de audio y sincronización de etiquetas |
| `TestService` | Preparación de preguntas (sorteo, aleatorización), aleatorización de opciones internas, corrección por tipo y cálculo de nota |
| `InicioService` | Verificación de acceso al módulo en el dashboard del alumno y persistencia del último módulo visitado |

---

## 🗄️ Base de datos

El esquema completo se define mediante **migraciones de Laravel**. Las tablas principales son:

```
usuarios ──┬── profesores ── modulos ──┬── preguntas ──── etiqueta_pregunta ── etiquetas
           │                           ├── tests ─────────┬── preguntas_tests
           └── alumnos ── modulos_alumnos                 ├── examenes
                                                          └── puntuaciones
```

Las relaciones pivot relevantes son:

- `modulos_alumnos`: N:M entre módulos y alumnos, con campo `tiene_acceso` para control granular de acceso.
- `preguntas_tests`: N:M entre preguntas y tests.
- `etiqueta_pregunta`: N:M entre preguntas y etiquetas.

El campo `contenido` de la tabla `preguntas` es **JSON polimórfico** (accedido como `AsArrayObject` de Eloquent) con estructura distinta según el tipo de pregunta.

---
## 🛠️ Tecnologías

<div align="center">

| Capa | Tecnología |
|------|------------|
| Backend | Laravel 11 · PHP 8.2 |
| Frontend | Blade · HTML5 · CSS3 |
| Interactividad cliente | Alpine.js · JavaScript |
| Base de datos | MySQL / MariaDB |
| Cola de jobs | Laravel Queue (driver: database) |
| Almacenamiento | Laravel Storage (disco public) |
| Correo | Gmail SMTP · Laravel Mail (ShouldQueue) |
| Despliegue | InfinityFree · Apache |

</div>

---

## ⚙️ Instalación

### Requisitos

- PHP >= 8.2
- Composer
- MySQL / MariaDB
- Apache con `mod_rewrite` habilitado

### Pasos

```bash
# 1. Clonar el repositorio
git clone https://github.com/Carlos17082005/TFG_TestApp.git
cd TFG_TestApp

# 2. Instalar dependencias PHP
composer install

# 3. Copiar el archivo de entorno
cp .env.example .env

# 4. Generar la clave de aplicación
php artisan key:generate
```
Edita el archivo `.env` con tus datos:

```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=testapp
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

# Correo (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_correo@gmail.com
MAIL_PASSWORD=tu_app_password   # Contraseña de aplicación de Google
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu_correo@gmail.com
MAIL_FROM_NAME="TestApp"

# Cola de jobs
QUEUE_CONNECTION=database
```

> ⚠️ **Gmail:** necesitas una **contraseña de aplicación**, no tu contraseña habitual. Genérala en: Google Account → Seguridad → Verificación en dos pasos → Contraseñas de aplicación.

```bash
# 5. Ejecutar migraciones
php artisan migrate

# 6. Crear enlace simbólico para el almacenamiento de audios
php artisan storage:link

# 7. Crear la tabla de jobs (cola de correos)
php artisan queue:table
php artisan migrate

# 8. Iniciar el servidor de desarrollo
php artisan serve
```

### Cola de correos

El correo de bienvenida al alumno se procesa de forma **asíncrona**. Necesitas el worker activo en una terminal separada:

```bash
# Procesar la cola de forma continua (recomendado)
php artisan queue:work

# Procesar un único job y parar (útil para pruebas puntuales)
php artisan queue:work --once

# Ver jobs fallidos
php artisan queue:failed

# Reintentar todos los jobs fallidos
php artisan queue:retry all

# Vaciar la tabla de jobs fallidos
php artisan queue:flush
```

---

## 🚀 Despliegue en InfinityFree

Desde el panel de InfinityFree:
1. Se crea un subdominio gratuito
2. Se crea la base de datos mediante phpMyAdmin y se ejecuta un SQL para crear tablas y datos (ya que no hay acceso a la consola)
3. Se suben los archivos del proyecto.
4. Se configura el archivo **.env** con los datos de la Base de Datos de InfinityFree 

```bash
DB_CONNECTION=mysql
DB_HOST=sq1208.infinityfree.com
DB_PORT=3306
DB_DATABASE=ife_41889059_db_duolingo
DB_USERNAME=1fe 41889059
DB_PASSWORD=TXI9NMNM3oc
```

5. Se configura el archivo **.htaccess** 

```bash
<IfModule mod_rewrite.c>
        RewriteEngine On

        # Redirigir todo el tráfico a la carpeta public
        RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

```bash
php artisan migrate --force
php artisan storage:link
```

6. Para el procesamiento de la cola de correos, configura un **cron job** en el panel:

```bash
php /ruta/absoluta/al/proyecto/artisan queue:work --stop-when-empty
```

---

## 📁 Estructura del proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── InicioController.php
│   │   ├── PreguntaController.php
│   │   ├── TestController.php
│   │   ├── RealizarTestController.php
│   │   ├── ProfesorModuloController.php
│   │   ├── ProfesorAlumnoController.php
│   │   ├── AlumnoModuloController.php
│   │   └── AlumnoTestController.php
│   └── Middleware/
│       ├── EsProfesor.php
│       ├── EsAlumno.php
│       ├── ModuloPerteneceProfesor.php
│       ├── ModuloPerteneceAlumno.php
│       └── AlumnoTieneAccesoExamen.php
├── Mail/
│   └── RegistroAlumno.php          # Mailable con ShouldQueue
├── Models/
│   ├── Usuario.php
│   ├── Profesor.php
│   ├── Alumno.php
│   ├── Modulo.php
│   ├── Pregunta.php                # contenido JSON polimórfico (AsArrayObject)
│   ├── Etiqueta.php
│   ├── Test.php
│   ├── Examen.php
│   └── Puntuacion.php
└── Services/
    ├── InicioService.php
    ├── PreguntaService.php
    └── TestService.php
database/
└── migrations/                     # Esquema completo de la BD
resources/views/                    # Plantillas Blade
routes/
└── web.php                         # Rutas agrupadas por rol con middleware
storage/app/public/
└── audios/preguntas/               # Audios subidos por los profesores
```

---

## 📄 Licencia

Proyecto desarrollado con fines académicos como Trabajo de Fin de Grado.  
IES · Familia Profesional: Informática y Comunicaciones · 2ºDAW · 2025/2026.

**© 2026, Equipo TestApp.** 

Este proyecto se distribuye bajo la licencia **Creative Commons Atribución-CompartirIgual 4.0 Internacional (CC BY-SA 4.0)**.
Para leer el texto legal completo, visita el [sitio web de Creative Commons](https://creativecommons.org).

<div align="center">
<br/>
<sub>Hecho con ❤️ por el equipo de TestApp</sub>
</div>
