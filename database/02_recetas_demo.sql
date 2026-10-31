-- Datos ficticios para probar búsqueda y categorías.
-- Ejecutar opcionalmente UNA SOLA VEZ después de 01_schema.sql.
USE reduccion_desperdicio;
INSERT INTO recetas (nombre, ingredientes, instrucciones) VALUES
('Tortilla de vegetales', 'Huevo, zanahoria, cebolla, espinaca', 'Lavar los vegetales, picarlos y cocinarlos. Agregar el huevo batido y cocinar completamente.'),
('Batido de frutas maduras', 'Banano, fresa, leche o agua', 'Lavar las frutas y licuarlas con leche o agua. Consumir de inmediato.'),
('Arroz salteado', 'Arroz cocido, zanahoria, brócoli, cebolla', 'Calentar bien el arroz previamente refrigerado y saltear con vegetales hasta cocinar.');
