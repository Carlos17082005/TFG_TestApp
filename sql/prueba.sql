-- 2. MODULOS (5)
INSERT INTO modulos (ciclo, modulo, id_profesor) VALUES
('DAM', 'Ingles', 'p.garcia@edu.es'),
('DAM', 'Economia', 'm.rodriguez@edu.es');

-- 5. PREGUNTAS
INSERT INTO preguntas (contenido, ciclo, modulo) VALUES
('{"pregunta": "Desktop organisers are programs that require desktop computers.", "tipo": "tf", "respuesta": "False"}', 'DAM', 'Ingles'),
('{"pregunta": "Computers are sometimes used to monitor systems that previously needed human supervision.", "tipo": "tf", "respuesta": "True"}', 'DAM', 'Ingles'),
('{"pregunta": "Networking is a way of allowing systems to communicate and share resources.", "tipo": "tf", "respuesta": "True"}', 'DAM', 'Ingles'),
('{"pregunta": "The use of computers prevents people from being creative.", "tipo": "tf", "respuesta": "False"}', 'DAM', 'Ingles'),
('{"pregunta": "Computer users do not have much influence over the way that computing develops.", "tipo": "tf", "respuesta": "False"}', 'DAM', 'Ingles'),
('{"pregunta": "Match each item (1–10) with its function (a–j).", "tipo": "conecta", "div-1": {"1": "RAM", "2": "Processor", "3": "Mouse", "4": "Clock", "5": "USB data stick", "6": "Monitor", "7": "Keyboard", "8": "DVD-ROM drive", "9": "Cache", "10": "WiFi"}, "div-2": {"a": "controls the pointer", "b": "inputs data through keys like a typewriter", "c": "displays the output from a computer", "d": "reads DVD-ROMs", "e": "stores randomly accessible data", "f": "access networks including the internet without cables", "g": "provides performance increase by storing frequently read", "h": "provides extremely fast storage for data", "i": "controls the timing of signals in the computer", "j": "controls all the operations in a computer"}, "respuesta": {"1": "e", "2": "j", "3": "a", "4": "i", "5": "h", "6": "c", "7": "b", "8": "d", "9": "g", "10": "f"}}', 'DAM', 'Ingles'),
('{"pregunta": {"cadena1": "The CPU is a chip", "cadena2": "the computer."}, "tipo": "texto", "respuesta": "inside"}', 'DAM', 'Ingles'),
('{"pregunta": {"cadena1": "Emails are sent", "cadena2": "the recipient."}, "tipo": "texto", "respuesta": "from"}', 'DAM', 'Ingles'),
('{"pregunta": {"cadena1": "You can sign", "cadena2": "online-banking."}, "tipo": "texto", "respuesta": "into"}', 'DAM', 'Ingles'),
('{"pregunta": {"cadena1": "I upload videos to the internet", "cadena2": "my smartphone."}, "tipo": "texto", "respuesta": "from"}', 'DAM', 'Ingles'),

('{"pregunta": "¿Los clientes son los compradores habituales de los bienes y/o servicios que comercializa la empresa?", "tipo": "tf", "respuesta": "True"}', 'DAM', 'Economia'),
('{"pregunta": "¿La cuenta clientes es un?", "opciones": {"a": "ACTIVO", "b": "PASIVO", "c": "PATRIMONIO NETO", "d": "GASTO", "e": "INGRESO"}, "respuesta": "a"}', 'DAM', 'Economia'),
('{"pregunta": "¿Qué nombre reciben los compradores habituales de los bienes y/o servicios que comercializa la empresa?", "opciones": {"a": "Acreedores", "b": "Clientes", "c": "Deudores", "d": "Proveedores"}, "respuesta": "b"}', 'DAM', 'Economia'),
('{"pregunta": "Si vendemos a un cliente género valorado en 1.000 u.m. (unidades monetarias) si el tipo de IVA es el general ¿cuál será el importe total que nos debe el cliente?", "tipo": "number", "respuesta": "1210"}', 'DAM', 'Economia'),
('{"pregunta": "Si en una factura emitida a un cliente el importe total son 1.210 u.m. (unidades monetarias) si el tipo de IVA es el general ¿cuál es el importe de la base imponible?", "tipo": "number", "respuesta": "1000"}', 'DAM', 'Economia');

-- 6. TESTS
INSERT INTO tests (nombre, ciclo, modulo) VALUES
('Examen de prueba ingles', 'DAM', 'Ingles'),
('Examen de prueba economia', 'DAM', 'Economia');

-- 7. PREGUNTAS_TESTS
INSERT INTO preguntas_tests (id_test, id_pregunta) VALUES
(6, 11), (6, 12), (6, 13), (6, 14), (6, 15), (6, 16), (6, 17), (6, 18), (6, 19), (6, 20), (7, 21), (7, 22), (7, 23), (7, 24), (7, 25);