<div align="center">

<br/>

# 🎓 TestApp
### *Proyecto "Duolingo"*

**Plataforma educativa Full Stack para la creación, realización y evaluación de tests formativos**

<br/>

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Blade](https://img.shields.io/badge/Blade-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

<br/>

</div>

---

## 👥 Participantes

<div align="center">

| 👤 Nombre |
|-----------|
| Álvaro Claudio de las Mozas |
| Carlos Gabriel García Guzmán |
| Miguel Ángel Durán Soto |

</div>

---

## 📋 Descripción del Proyecto

**TestApp** es una aplicación web educativa que conecta profesores y alumnos dentro de módulos formativos. Los profesores pueden diseñar preguntas y tests personalizados; los alumnos pueden practicar, evaluarse y consultar su progreso en tiempo real.

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

## 🛠️ Tecnologías

<div align="center">

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 12 · PHP 8.2 |
| Frontend | Blade · HTML · CSS |
| Interactividad | Alpine.js · JavaScript |
| Base de datos | MySQL |

</div>

---

## ⚙️ Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/testapp.git
cd testapp

# 2. Instalar dependencias
composer install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar la base de datos en .env y ejecutar migraciones
php artisan migrate

# 5. Iniciar el servidor
php artisan serve
```

---

## 📄 Licencia

Este proyecto ha sido desarrollado con fines académicos como **Trabajo de Fin de Grado**.

<div align="center">
<br/>
<sub>Hecho con ❤️ por el equipo de TestApp</sub>
</div>
