DROP DATABASE IF EXISTS TestApp;
-- Añadimos la codificación utf8mb4 para evitar los errores 1366 con las tildes
CREATE DATABASE TestApp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE TestApp;

CREATE TABLE profesores (
   id_profesor varchar(40), # correo del profesor
   nombre varchar(40) NOT NULL,
   apellidos varchar(40) NOT NULL,
   password varchar(40) NOT NULL,
   PRIMARY KEY (id_profesor)
);

CREATE TABLE modulos (
   ciclo varchar(40),
   modulo varchar(40),
   id_profesor varchar(40),
   PRIMARY KEY (ciclo, modulo),
   FOREIGN KEY (id_profesor) REFERENCES profesores(id_profesor)
);

CREATE TABLE alumnos (
   id_alumno varchar(40), # correo del alumno
   nombre varchar(40) NOT NULL,
   apellidos varchar(40) NOT NULL,
   password varchar(40) NOT NULL,
   validado int(1) DEFAULT 0, # 0=no validado/ 1=validado
   PRIMARY KEY (id_alumno)
);

CREATE TABLE matriculado (
   id_alumno varchar(40), # correo del alumno
   ciclo varchar(40),
   modulo varchar(40),
   PRIMARY KEY (id_alumno,ciclo,modulo),
   FOREIGN KEY (id_alumno) REFERENCES alumnos(id_alumno),
   FOREIGN KEY (ciclo, modulo) REFERENCES modulos(ciclo, modulo)
);

CREATE TABLE tests (
   id_test INT AUTO_INCREMENT,
   descripcion varchar(300),
   nombre varchar(40) NOT NULL,
   ciclo varchar(40),
   modulo varchar(40),
   PRIMARY KEY (id_test),
   FOREIGN KEY (ciclo, modulo) REFERENCES modulos(ciclo, modulo)
);

CREATE TABLE preguntas (
   id_pregunta INT AUTO_INCREMENT,
   contenido JSON NOT NULL,
   ciclo varchar(40),
   modulo varchar(40),
   PRIMARY KEY (id_pregunta),
   FOREIGN KEY (ciclo, modulo) REFERENCES modulos(ciclo, modulo)
);

CREATE TABLE preguntas_tests (
   id_test INT,
   id_pregunta INT,
   PRIMARY KEY (id_test,id_pregunta),
   FOREIGN KEY (id_test) REFERENCES tests(id_test),
   FOREIGN KEY (id_pregunta) REFERENCES preguntas(id_pregunta)
);

CREATE TABLE puntuacion (
   id_alumno VARCHAR(40),
   id_test INT,
   -- DATETIME guarda YYYY-MM-DD HH:MM:SS
   fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
   -- CORRECCIÓN: NOT NULL debe ir antes del CHECK
   puntuacion INT(3) NOT NULL CHECK (puntuacion >= 0 AND puntuacion <= 100),
   PRIMARY KEY (id_alumno, id_test, fecha),
   FOREIGN KEY (id_alumno) REFERENCES alumnos(id_alumno),
   FOREIGN KEY (id_test) REFERENCES tests(id_test)
);


-- 1. PROFESORES (5)
INSERT INTO profesores (id_profesor, nombre, apellidos, password) VALUES
('p.garcia@edu.es', 'Pablo', 'García', 'hash_pablo_123'),
('m.rodriguez@edu.es', 'Marta', 'Rodríguez', 'hash_marta_456'),
('j.lopez@edu.es', 'Juan', 'López', 'hash_juan_789'),
('s.sanchez@edu.es', 'Sara', 'Sánchez', 'hash_sara_012'),
('a.martinez@edu.es', 'Andrés', 'Martínez', 'hash_andres_345');

-- 2. MODULOS (5)
INSERT INTO modulos (ciclo, modulo, id_profesor) VALUES
('DAM', 'Programación', 'p.garcia@edu.es'),
('DAM', 'Bases de Datos', 'm.rodriguez@edu.es'),
('DAW', 'Desarrollo Web Cliente', 'j.lopez@edu.es'),
('DAW', 'Desarrollo Web Servidor', 's.sanchez@edu.es'),
('ASIR', 'Seguridad Informática', 'a.martinez@edu.es');

-- 3. ALUMNOS (20)
INSERT INTO alumnos (id_alumno, nombre, apellidos, password, validado) VALUES
('al01@edu.es', 'Lucas', 'Pérez', 'pass01', 1), ('al02@edu.es', 'Ana', 'Ruiz', 'pass02', 1),
('al03@edu.es', 'Mario', 'Sanz', 'pass03', 1), ('al04@edu.es', 'Lucía', 'Díaz', 'pass04', 1),
('al05@edu.es', 'Hugo', 'Blanco', 'pass05', 1), ('al06@edu.es', 'Eva', 'Castro', 'pass06', 1),
('al07@edu.es', 'Iván', 'Gil', 'pass07', 0), ('al08@edu.es', 'Julia', 'Mora', 'pass08', 1),
('al09@edu.es', 'Leo', 'Vega', 'pass09', 1), ('al10@edu.es', 'Clara', 'Soler', 'pass10', 0),
('al11@edu.es', 'Noa', 'Ramos', 'pass11', 1), ('al12@edu.es', 'Eric', 'Vidal', 'pass12', 1),
('al13@edu.es', 'Alba', 'Pascual', 'pass13', 1), ('al14@edu.es', 'Raúl', 'Soto', 'pass14', 1),
('al15@edu.es', 'Inés', 'Ortega', 'pass15', 1), ('al16@edu.es', 'Marc', 'Ibáñez', 'pass16', 0),
('al17@edu.es', 'Irene', 'Cano', 'pass17', 1), ('al18@edu.es', 'Aitor', 'Marín', 'pass18', 1),
('al19@edu.es', 'Elena', 'Cruz', 'pass19', 1), ('al20@edu.es', 'Pau', 'Navarro', 'pass20', 1);

-- 4. MATRICULADO
INSERT INTO matriculado (id_alumno, ciclo, modulo) VALUES
('al01@edu.es', 'DAM', 'Programación'), ('al01@edu.es', 'DAM', 'Bases de Datos'),
('al02@edu.es', 'DAM', 'Programación'), ('al03@edu.es', 'DAW', 'Desarrollo Web Cliente'),
('al04@edu.es', 'ASIR', 'Seguridad Informática'), ('al05@edu.es', 'DAW', 'Desarrollo Web Servidor');

-- 5. PREGUNTAS
INSERT INTO preguntas (contenido, ciclo, modulo) VALUES
('{"pregunta": "¿Qué es un bucle?", "opciones": {"a": "Estructura repetitiva", "b": "Variable", "c": "Función"}, "respuesta": "a"}', 'DAM', 'Programación'),
('{"pregunta": "¿Tipo de dato entero?", "opciones": {"a": "float", "b": "int", "c": "string"}, "respuesta": "b"}', 'DAM', 'Programación'),
('{"pregunta": "¿Qué significa SQL?", "opciones": {"a": "Strong Query Language", "b": "Structured Query Language", "c": "Simple Queue List"}, "respuesta": "b"}', 'DAM', 'Bases de Datos'),
('{"pregunta": "¿Clave primaria es única?", "opciones": {"a": "No", "b": "A veces", "c": "Sí"}, "respuesta": "c"}', 'DAM', 'Bases de Datos'),
('{"pregunta": "¿Qué usa JS?", "opciones": {"a": "Navegador", "b": "Kernel", "c": "Firmware"}, "respuesta": "a"}', 'DAW', 'Desarrollo Web Cliente'),
('{"pregunta": "¿Qué es el DOM?", "opciones": {"a": "Un objeto", "b": "Un virus", "c": "Un estilo"}, "respuesta": "a"}', 'DAW', 'Desarrollo Web Cliente'),
('{"pregunta": "¿PHP es de servidor?", "opciones": {"a": "No", "b": "Sí", "c": "Solo local"}, "respuesta": "b"}', 'DAW', 'Desarrollo Web Servidor'),
('{"pregunta": "¿Qué es Node.js?", "opciones": {"a": "Entorno JS", "b": "Base de datos", "c": "Editor"}, "respuesta": "a"}', 'DAW', 'Desarrollo Web Servidor'),
('{"pregunta": "¿Qué es un Firewall?", "opciones": {"a": "Sistema de muros", "b": "Filtro de tráfico", "c": "Antivirus"}, "respuesta": "b"}', 'ASIR', 'Seguridad Informática'),
('{"pregunta": "¿Qué es HTTPS?", "opciones": {"a": "Protocolo seguro", "b": "Un cable", "c": "Una IP"}, "respuesta": "a"}', 'ASIR', 'Seguridad Informática');

-- 6. TESTS
INSERT INTO tests (nombre, ciclo, modulo) VALUES
('Examen Parcial Programación', 'DAM', 'Programación'),
('Test de SQL Básico', 'DAM', 'Bases de Datos'),
('Frontend JS Test', 'DAW', 'Desarrollo Web Cliente'),
('Backend PHP/Node', 'DAW', 'Desarrollo Web Servidor'),
('Auditoría de Redes', 'ASIR', 'Seguridad Informática');

-- 7. PREGUNTAS_TESTS
INSERT INTO preguntas_tests (id_test, id_pregunta) VALUES
(1, 1), (1, 2), (2, 3), (2, 4), (3, 5), (3, 6), (4, 7), (4, 8), (5, 9), (5, 10);

-- 8. PUNTUACION
INSERT INTO puntuacion (id_alumno, id_test, puntuacion) VALUES
('al01@edu.es', 1, 85),
('al01@edu.es', 2, 90),
('al02@edu.es', 1, 70),
('al03@edu.es', 3, 100),
('al04@edu.es', 5, 45);