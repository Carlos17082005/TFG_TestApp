<?php

namespace Database\Seeders;

use App\Models\Etiqueta;
use App\Models\Modulo;
use App\Models\Pregunta;
use Illuminate\Database\Seeder;

class PreguntaSeeder extends Seeder
{
    public function run(): void
    {
        $modulos   = Modulo::all()->keyBy('modulo');
        $etiquetas = Etiqueta::all()->keyBy('nombre');

        // ── Helper: sync etiquetas por nombre ───────────────────────────────
        $syncEtiquetas = function (Pregunta $pregunta, array $nombres) use ($etiquetas) {
            $ids = collect($nombres)
                ->map(fn($n) => $etiquetas[$n]->id_etiqueta ?? null)
                ->filter()
                ->values()
                ->toArray();
            if (!empty($ids)) {
                $pregunta->listaEtiquetas()->sync($ids);
            }
        };

        // ════════════════════════════════════════════════════════════════════
        // MÓDULO 1 — Programación
        // ════════════════════════════════════════════════════════════════════
        $mProg = $modulos['Programación'];

        $preguntasProg = [
            // 1
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿PHP es un lenguaje de tipado fuerte?', 'respuesta' => 'falso'], 'etiquetas' => ['variables']],
            // 2
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿Los arrays en PHP pueden contener valores de distintos tipos?', 'respuesta' => 'verdadero'], 'etiquetas' => ['arrays']],
            // 3
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿Una función puede devolver más de un valor en PHP?', 'respuesta' => 'falso'], 'etiquetas' => ['funciones']],
            // 4
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿En PHP los índices de los arrays empiezan en 0?', 'respuesta' => 'verdadero'], 'etiquetas' => ['arrays']],
            // 5
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué función se usa para contar los elementos de un array en PHP?', 'respuesta' => 'count'], 'etiquetas' => ['arrays', 'funciones']],
            // 6
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué palabra clave se usa para crear una clase en PHP?', 'respuesta' => 'class'], 'etiquetas' => ['poo']],
            // 7
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Cómo se llama el método que se ejecuta automáticamente al crear un objeto?', 'respuesta' => '__construct'], 'etiquetas' => ['poo']],
            // 8
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué bucle se usa cuando no sabemos el número exacto de iteraciones?', 'respuesta' => 'while'], 'etiquetas' => ['bucles']],
            // 9
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál de estas es la forma correcta de declarar una variable en PHP?', 'opciones' => ['$nombre = "Juan"', 'var nombre = "Juan"', 'nombre := "Juan"', 'let nombre = "Juan"'], 'respuesta' => '$nombre = "Juan"'], 'etiquetas' => ['variables']],
            // 10
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué operador se usa para concatenar cadenas en PHP?', 'opciones' => ['.', '+', '&', '|'], 'respuesta' => '.'], 'etiquetas' => ['variables']],
            // 11
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál es el tipo de bucle que siempre se ejecuta al menos una vez?', 'opciones' => ['do-while', 'while', 'for', 'foreach'], 'respuesta' => 'do-while'], 'etiquetas' => ['bucles']],
            // 12
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué modificador de acceso permite acceder a un atributo solo desde la propia clase?', 'opciones' => ['private', 'public', 'protected', 'static'], 'respuesta' => 'private'], 'etiquetas' => ['poo']],
            // 13
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál de estas funciones ordena un array en PHP?', 'opciones' => ['sort()', 'order()', 'arrange()', 'asort()'], 'respuesta' => 'sort()'], 'etiquetas' => ['arrays', 'funciones']],
            // 14
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué significa POO?', 'opciones' => ['Programación Orientada a Objetos', 'Programación Orientada a Operaciones', 'Procesamiento de Objetos Optimizados', 'Protocolo de Objetos Operativos'], 'respuesta' => 'Programación Orientada a Objetos'], 'etiquetas' => ['poo']],
            // 15
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál de los siguientes bucles itera sobre los elementos de un array directamente?', 'opciones' => ['foreach', 'for', 'while', 'do-while'], 'respuesta' => 'foreach'], 'etiquetas' => ['bucles', 'arrays']],
            // 16
            ['tipo' => 'conecta', 'contenido' => ['enunciado' => 'Relaciona cada concepto con su descripción', 'parejas' => [['a' => 'Herencia', 'b' => 'Una clase extiende a otra'], ['a' => 'Encapsulamiento', 'b' => 'Ocultar los datos internos'], ['a' => 'Polimorfismo', 'b' => 'Un método actúa de forma diferente'], ['a' => 'Abstracción', 'b' => 'Modelar conceptos del mundo real']]], 'etiquetas' => ['poo']],
            // 17
            ['tipo' => 'conecta', 'contenido' => ['enunciado' => 'Relaciona el tipo de bucle con su uso principal', 'parejas' => [['a' => 'for', 'b' => 'Número de iteraciones conocido'], ['a' => 'while', 'b' => 'Condición evaluada al inicio'], ['a' => 'do-while', 'b' => 'Se ejecuta al menos una vez'], ['a' => 'foreach', 'b' => 'Recorrer colecciones']]], 'etiquetas' => ['bucles']],
            // 18
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué palabra clave se usa en PHP para heredar de otra clase?', 'respuesta' => 'extends'], 'etiquetas' => ['poo']],
            // 19
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿El bucle foreach puede modificar los valores del array original si se usa por referencia?', 'respuesta' => 'verdadero'], 'etiquetas' => ['bucles', 'arrays']],
            // 20
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué función elimina el último elemento de un array en PHP?', 'respuesta' => 'array_pop'], 'etiquetas' => ['arrays', 'funciones']],
        ];

        foreach ($preguntasProg as $data) {
            $p = Pregunta::create(['tipo' => $data['tipo'], 'contenido' => $data['contenido'], 'id_modulo' => $mProg->id_modulo]);
            $syncEtiquetas($p, $data['etiquetas']);
        }

        // ════════════════════════════════════════════════════════════════════
        // MÓDULO 2 — Bases de Datos
        // ════════════════════════════════════════════════════════════════════
        $mBBDD = $modulos['Bases de Datos'];

        $preguntasBBDD = [
            // 1
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿La clave primaria puede contener valores NULL?', 'respuesta' => 'falso'], 'etiquetas' => ['sql']],
            // 2
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿SELECT * devuelve todas las columnas de una tabla?', 'respuesta' => 'verdadero'], 'etiquetas' => ['sql']],
            // 3
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿Un INNER JOIN devuelve los registros sin coincidencia de ambas tablas?', 'respuesta' => 'falso'], 'etiquetas' => ['joins']],
            // 4
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿La Primera Forma Normal elimina grupos repetidos?', 'respuesta' => 'verdadero'], 'etiquetas' => ['normalizacion']],
            // 5
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué cláusula se usa para filtrar filas en SQL?', 'respuesta' => 'WHERE'], 'etiquetas' => ['sql']],
            // 6
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué instrucción SQL se usa para insertar datos en una tabla?', 'respuesta' => 'INSERT INTO'], 'etiquetas' => ['sql']],
            // 7
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué cláusula se usa para ordenar los resultados de una consulta?', 'respuesta' => 'ORDER BY'], 'etiquetas' => ['sql']],
            // 8
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Cómo se llama la dependencia que existe cuando un atributo depende de la clave primaria completa?', 'respuesta' => 'dependencia funcional'], 'etiquetas' => ['normalizacion']],
            // 9
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué tipo de JOIN devuelve todos los registros de la tabla izquierda?', 'opciones' => ['LEFT JOIN', 'INNER JOIN', 'CROSS JOIN', 'RIGHT JOIN'], 'respuesta' => 'LEFT JOIN'], 'etiquetas' => ['joins']],
            // 10
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué función SQL cuenta el número de filas?', 'opciones' => ['COUNT()', 'SUM()', 'AVG()', 'MAX()'], 'respuesta' => 'COUNT()'], 'etiquetas' => ['sql']],
            // 11
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál de estas no es una restricción de integridad en SQL?', 'opciones' => ['LOOP', 'PRIMARY KEY', 'FOREIGN KEY', 'UNIQUE'], 'respuesta' => 'LOOP'], 'etiquetas' => ['sql']],
            // 12
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué cláusula agrupa filas con valores iguales?', 'opciones' => ['GROUP BY', 'ORDER BY', 'HAVING', 'WHERE'], 'respuesta' => 'GROUP BY'], 'etiquetas' => ['sql']],
            // 13
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuántas formas normales principales existen?', 'opciones' => ['3', '2', '4', '5'], 'respuesta' => '3'], 'etiquetas' => ['normalizacion']],
            // 14
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué sentencia elimina todos los datos de una tabla sin borrarla?', 'opciones' => ['TRUNCATE', 'DELETE', 'DROP', 'REMOVE'], 'respuesta' => 'TRUNCATE'], 'etiquetas' => ['sql']],
            // 15
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué JOIN devuelve solo las filas con coincidencia en ambas tablas?', 'opciones' => ['INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'FULL OUTER JOIN'], 'respuesta' => 'INNER JOIN'], 'etiquetas' => ['joins']],
            // 16
            ['tipo' => 'conecta', 'contenido' => ['enunciado' => 'Relaciona el comando SQL con su función', 'parejas' => [['a' => 'SELECT', 'b' => 'Consultar datos'], ['a' => 'INSERT', 'b' => 'Añadir registros'], ['a' => 'UPDATE', 'b' => 'Modificar registros'], ['a' => 'DELETE', 'b' => 'Eliminar registros']]], 'etiquetas' => ['sql']],
            // 17
            ['tipo' => 'conecta', 'contenido' => ['enunciado' => 'Relaciona cada tipo de JOIN con su comportamiento', 'parejas' => [['a' => 'INNER JOIN', 'b' => 'Solo coincidencias'], ['a' => 'LEFT JOIN', 'b' => 'Todos de la izquierda'], ['a' => 'RIGHT JOIN', 'b' => 'Todos de la derecha'], ['a' => 'CROSS JOIN', 'b' => 'Producto cartesiano']]], 'etiquetas' => ['joins']],
            // 18
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué cláusula filtra grupos en SQL (equivalente a WHERE para GROUP BY)?', 'respuesta' => 'HAVING'], 'etiquetas' => ['sql']],
            // 19
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿La clave foránea garantiza la integridad referencial?', 'respuesta' => 'verdadero'], 'etiquetas' => ['sql', 'normalizacion']],
            // 20
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué forma normal elimina las dependencias transitivas?', 'respuesta' => 'tercera forma normal'], 'etiquetas' => ['normalizacion']],
        ];

        foreach ($preguntasBBDD as $data) {
            $p = Pregunta::create(['tipo' => $data['tipo'], 'contenido' => $data['contenido'], 'id_modulo' => $mBBDD->id_modulo]);
            $syncEtiquetas($p, $data['etiquetas']);
        }

        // ════════════════════════════════════════════════════════════════════
        // MÓDULO 3 — Desarrollo Web
        // ════════════════════════════════════════════════════════════════════
        $mWeb = $modulos['Desarrollo Web'];

        $preguntasWeb = [
            // 1
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿HTML es un lenguaje de programación?', 'respuesta' => 'falso'], 'etiquetas' => ['html']],
            // 2
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿La etiqueta <br> sirve para hacer un salto de línea en HTML?', 'respuesta' => 'verdadero'], 'etiquetas' => ['html']],
            // 3
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿CSS puede aplicarse directamente dentro de un elemento HTML?', 'respuesta' => 'verdadero'], 'etiquetas' => ['css']],
            // 4
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿JavaScript puede modificar el DOM de una página web?', 'respuesta' => 'verdadero'], 'etiquetas' => ['javascript']],
            // 5
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué etiqueta HTML se usa para incluir un fichero CSS externo?', 'respuesta' => 'link'], 'etiquetas' => ['html', 'css']],
            // 6
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué propiedad CSS controla el color de fondo de un elemento?', 'respuesta' => 'background-color'], 'etiquetas' => ['css']],
            // 7
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué método JavaScript se usa para seleccionar un elemento por su ID?', 'respuesta' => 'getElementById'], 'etiquetas' => ['javascript']],
            // 8
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué etiqueta define el título visible en la pestaña del navegador?', 'respuesta' => 'title'], 'etiquetas' => ['html']],
            // 9
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál de estos no es un tipo de selector CSS?', 'opciones' => ['Selector de función', 'Selector de clase', 'Selector de ID', 'Selector de elemento'], 'respuesta' => 'Selector de función'], 'etiquetas' => ['css']],
            // 10
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué atributo HTML especifica la URL de un enlace?', 'opciones' => ['href', 'src', 'link', 'url'], 'respuesta' => 'href'], 'etiquetas' => ['html']],
            // 11
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál de los siguientes es un framework de CSS?', 'opciones' => ['Bootstrap', 'React', 'Node.js', 'Vue'], 'respuesta' => 'Bootstrap'], 'etiquetas' => ['css']],
            // 12
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué evento JavaScript se dispara al hacer click en un elemento?', 'opciones' => ['onclick', 'onhover', 'onfocus', 'onchange'], 'respuesta' => 'onclick'], 'etiquetas' => ['javascript']],
            // 13
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué valor de display convierte un elemento en flexible?', 'opciones' => ['flex', 'block', 'inline', 'grid'], 'respuesta' => 'flex'], 'etiquetas' => ['css']],
            // 14
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál es la etiqueta correcta para incluir JavaScript en HTML?', 'opciones' => ['<script>', '<js>', '<javascript>', '<code>'], 'respuesta' => '<script>'], 'etiquetas' => ['html', 'javascript']],
            // 15
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué unidad CSS es relativa al tamaño de fuente del elemento padre?', 'opciones' => ['em', 'px', 'vh', 'cm'], 'respuesta' => 'em'], 'etiquetas' => ['css']],
            // 16
            ['tipo' => 'conecta', 'contenido' => ['enunciado' => 'Relaciona la etiqueta HTML con su función', 'parejas' => [['a' => '<h1>', 'b' => 'Encabezado principal'], ['a' => '<p>', 'b' => 'Párrafo'], ['a' => '<a>', 'b' => 'Enlace'], ['a' => '<img>', 'b' => 'Imagen']]], 'etiquetas' => ['html']],
            // 17
            ['tipo' => 'conecta', 'contenido' => ['enunciado' => 'Relaciona la propiedad CSS con lo que controla', 'parejas' => [['a' => 'margin', 'b' => 'Espacio exterior al elemento'], ['a' => 'padding', 'b' => 'Espacio interior al elemento'], ['a' => 'border', 'b' => 'Borde del elemento'], ['a' => 'font-size', 'b' => 'Tamaño del texto']]], 'etiquetas' => ['css']],
            // 18
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué método se usa en JavaScript para añadir un evento a un elemento?', 'respuesta' => 'addEventListener'], 'etiquetas' => ['javascript']],
            // 19
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿Las etiquetas HTML distinguen entre mayúsculas y minúsculas?', 'respuesta' => 'falso'], 'etiquetas' => ['html']],
            // 20
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué propiedad CSS se usa para ocultar un elemento manteniendo su espacio?', 'respuesta' => 'visibility'], 'etiquetas' => ['css']],
        ];

        foreach ($preguntasWeb as $data) {
            $p = Pregunta::create(['tipo' => $data['tipo'], 'contenido' => $data['contenido'], 'id_modulo' => $mWeb->id_modulo]);
            $syncEtiquetas($p, $data['etiquetas']);
        }

        // ════════════════════════════════════════════════════════════════════
        // MÓDULO 4 — Sistemas Operativos
        // ════════════════════════════════════════════════════════════════════
        $mSO = $modulos['Sistemas Operativos'];

        $preguntasSO = [
            // 1
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿Linux es un sistema operativo de código abierto?', 'respuesta' => 'verdadero'], 'etiquetas' => ['linux']],
            // 2
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿El comando rm -rf elimina archivos y carpetas de forma recursiva sin confirmación?', 'respuesta' => 'verdadero'], 'etiquetas' => ['comandos']],
            // 3
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿El superusuario en Linux se llama "admin"?', 'respuesta' => 'falso'], 'etiquetas' => ['linux']],
            // 4
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿El comando ls muestra los archivos del directorio actual?', 'respuesta' => 'verdadero'], 'etiquetas' => ['comandos']],
            // 5
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué comando se usa para cambiar de directorio en Linux?', 'respuesta' => 'cd'], 'etiquetas' => ['comandos']],
            // 6
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué comando muestra el directorio actual en Linux?', 'respuesta' => 'pwd'], 'etiquetas' => ['comandos']],
            // 7
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Cómo se llama el superusuario en Linux?', 'respuesta' => 'root'], 'etiquetas' => ['linux']],
            // 8
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué comando crea un directorio en Linux?', 'respuesta' => 'mkdir'], 'etiquetas' => ['comandos']],
            // 9
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál de estos no es una distribución de Linux?', 'opciones' => ['macOS', 'Ubuntu', 'Debian', 'Fedora'], 'respuesta' => 'macOS'], 'etiquetas' => ['linux']],
            // 10
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué comando muestra los procesos en ejecución?', 'opciones' => ['ps', 'ls', 'cat', 'grep'], 'respuesta' => 'ps'], 'etiquetas' => ['comandos']],
            // 11
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál es el permiso numérico que da acceso total al propietario?', 'opciones' => ['7', '5', '6', '4'], 'respuesta' => '7'], 'etiquetas' => ['linux']],
            // 12
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué comando se usa para ver el contenido de un archivo de texto?', 'opciones' => ['cat', 'ls', 'cd', 'rm'], 'respuesta' => 'cat'], 'etiquetas' => ['comandos']],
            // 13
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál de estas opciones del comando chmod otorga permisos de ejecución al propietario?', 'opciones' => ['u+x', 'o+x', 'g+x', 'a-x'], 'respuesta' => 'u+x'], 'etiquetas' => ['linux', 'comandos']],
            // 14
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué comando busca texto dentro de ficheros?', 'opciones' => ['grep', 'find', 'ls', 'diff'], 'respuesta' => 'grep'], 'etiquetas' => ['comandos']],
            // 15
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Dónde se almacenan los archivos de configuración del sistema en Linux?', 'opciones' => ['/etc', '/bin', '/home', '/usr'], 'respuesta' => '/etc'], 'etiquetas' => ['linux']],
            // 16
            ['tipo' => 'conecta', 'contenido' => ['enunciado' => 'Relaciona el comando con su función', 'parejas' => [['a' => 'cp', 'b' => 'Copiar archivos'], ['a' => 'mv', 'b' => 'Mover o renombrar'], ['a' => 'rm', 'b' => 'Eliminar archivos'], ['a' => 'touch', 'b' => 'Crear archivo vacío']]], 'etiquetas' => ['comandos']],
            // 17
            ['tipo' => 'conecta', 'contenido' => ['enunciado' => 'Relaciona el directorio de Linux con su contenido habitual', 'parejas' => [['a' => '/home', 'b' => 'Carpetas personales de usuarios'], ['a' => '/etc', 'b' => 'Ficheros de configuración'], ['a' => '/bin', 'b' => 'Comandos básicos del sistema'], ['a' => '/var', 'b' => 'Datos variables (logs, etc.)']]], 'etiquetas' => ['linux']],
            // 18
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué comando instala paquetes en distribuciones basadas en Debian?', 'respuesta' => 'apt install'], 'etiquetas' => ['linux', 'comandos']],
            // 19
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿El símbolo ~ representa el directorio home del usuario actual en Linux?', 'respuesta' => 'verdadero'], 'etiquetas' => ['linux']],
            // 20
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué comando permite ver y cambiar los permisos de un archivo en Linux?', 'respuesta' => 'chmod'], 'etiquetas' => ['linux', 'comandos']],
        ];

        foreach ($preguntasSO as $data) {
            $p = Pregunta::create(['tipo' => $data['tipo'], 'contenido' => $data['contenido'], 'id_modulo' => $mSO->id_modulo]);
            $syncEtiquetas($p, $data['etiquetas']);
        }

        // ════════════════════════════════════════════════════════════════════
        // MÓDULO 5 — Entornos de Desarrollo
        // ════════════════════════════════════════════════════════════════════
        $mEdes = $modulos['Entornos de Desarrollo'];

        $preguntasEdes = [
            // 1
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿Git es un sistema de control de versiones distribuido?', 'respuesta' => 'verdadero'], 'etiquetas' => ['git']],
            // 2
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿El comando git push sube los cambios al repositorio remoto?', 'respuesta' => 'verdadero'], 'etiquetas' => ['git']],
            // 3
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿GitHub y Git son lo mismo?', 'respuesta' => 'falso'], 'etiquetas' => ['git']],
            // 4
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿Un IDE integra editor, depurador y compilador en una sola herramienta?', 'respuesta' => 'verdadero'], 'etiquetas' => ['ide']],
            // 5
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué comando de Git guarda los cambios en el área de preparación?', 'respuesta' => 'git add'], 'etiquetas' => ['git']],
            // 6
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué comando de Git registra los cambios en el repositorio local?', 'respuesta' => 'git commit'], 'etiquetas' => ['git']],
            // 7
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Cómo se llama la rama principal que se crea por defecto en Git?', 'respuesta' => 'main'], 'etiquetas' => ['git']],
            // 8
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué IDE de Microsoft es muy popular para desarrollo web?', 'respuesta' => 'Visual Studio Code'], 'etiquetas' => ['ide']],
            // 9
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál de estos comandos crea una nueva rama en Git?', 'opciones' => ['git branch nombre', 'git new nombre', 'git create nombre', 'git fork nombre'], 'respuesta' => 'git branch nombre'], 'etiquetas' => ['git']],
            // 10
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué hace git merge?', 'opciones' => ['Fusiona dos ramas', 'Elimina una rama', 'Crea una rama', 'Clona un repositorio'], 'respuesta' => 'Fusiona dos ramas'], 'etiquetas' => ['git']],
            // 11
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál no es un IDE?', 'opciones' => ['Notepad', 'IntelliJ IDEA', 'Visual Studio', 'Eclipse'], 'respuesta' => 'Notepad'], 'etiquetas' => ['ide']],
            // 12
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué archivo de Git especifica qué archivos no deben ser rastreados?', 'opciones' => ['.gitignore', '.gitconfig', '.gitkeep', '.gitnore'], 'respuesta' => '.gitignore'], 'etiquetas' => ['git']],
            // 13
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué comando descarga cambios del repositorio remoto y los fusiona?', 'opciones' => ['git pull', 'git fetch', 'git clone', 'git sync'], 'respuesta' => 'git pull'], 'etiquetas' => ['git']],
            // 14
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Cuál de las siguientes es una funcionalidad clave de un IDE?', 'opciones' => ['Depuración integrada', 'Gestión de redes', 'Edición de imágenes', 'Control de hardware'], 'respuesta' => 'Depuración integrada'], 'etiquetas' => ['ide']],
            // 15
            ['tipo' => 'multiple', 'contenido' => ['enunciado' => '¿Qué significa la sigla IDE?', 'opciones' => ['Integrated Development Environment', 'Internet Data Exchange', 'Internal Debug Engine', 'Integrated Design Editor'], 'respuesta' => 'Integrated Development Environment'], 'etiquetas' => ['ide']],
            // 16
            ['tipo' => 'conecta', 'contenido' => ['enunciado' => 'Relaciona el comando Git con su función', 'parejas' => [['a' => 'git init', 'b' => 'Inicializa un repositorio'], ['a' => 'git clone', 'b' => 'Clona un repositorio remoto'], ['a' => 'git status', 'b' => 'Muestra el estado de los archivos'], ['a' => 'git log', 'b' => 'Muestra el historial de commits']]], 'etiquetas' => ['git']],
            // 17
            ['tipo' => 'conecta', 'contenido' => ['enunciado' => 'Relaciona el IDE con el lenguaje para el que está optimizado principalmente', 'parejas' => [['a' => 'IntelliJ IDEA', 'b' => 'Java'], ['a' => 'PyCharm', 'b' => 'Python'], ['a' => 'VS Code', 'b' => 'Múltiples lenguajes'], ['a' => 'Xcode', 'b' => 'Swift']]], 'etiquetas' => ['ide']],
            // 18
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué comando inicializa un repositorio Git en un directorio?', 'respuesta' => 'git init'], 'etiquetas' => ['git']],
            // 19
            ['tipo' => 'booleana', 'contenido' => ['enunciado' => '¿git stash guarda temporalmente los cambios sin hacer commit?', 'respuesta' => 'verdadero'], 'etiquetas' => ['git']],
            // 20
            ['tipo' => 'texto', 'contenido' => ['enunciado' => '¿Qué comando muestra el historial de commits en Git?', 'respuesta' => 'git log'], 'etiquetas' => ['git']],
        ];

        foreach ($preguntasEdes as $data) {
            $p = Pregunta::create(['tipo' => $data['tipo'], 'contenido' => $data['contenido'], 'id_modulo' => $mEdes->id_modulo]);
            $syncEtiquetas($p, $data['etiquetas']);
        }
    }
}
