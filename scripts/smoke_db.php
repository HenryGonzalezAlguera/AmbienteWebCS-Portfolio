<?php
declare(strict_types=1);

// Comprobación destructiva SOLO para la base descartable de CI.
if (PHP_SAPI !== 'cli' || getenv('CI_SMOKE') !== '1' ||
    getenv('DB_NAME') !== 'reduccion_desperdicio_ci') {
    fwrite(STDERR, "Solo se permite ejecutar con CI_SMOKE=1 y DB_NAME=reduccion_desperdicio_ci.\n");
    exit(2);
}

require __DIR__ . '/../Avance2/db_config.php';

$schema = file_get_contents(__DIR__ . '/../database/01_schema.sql');
if ($schema === false) {
    throw new RuntimeException('No se pudo leer el esquema SQL.');
}
$schema = str_replace('reduccion_desperdicio', 'reduccion_desperdicio_ci', $schema);
$conn->multi_query($schema);
do {
    $result = $conn->store_result();
    if ($result instanceof mysqli_result) {
        $result->free();
    }
} while ($conn->more_results() && $conn->next_result());

foreach (['usuarios', 'recetas', 'donaciones', 'recuperacion_contrasena'] as $table) {
    $result = $conn->query('SHOW TABLES LIKE ' . "'" . $table . "'");
    if ($result->num_rows !== 1) {
        throw new RuntimeException('Tabla no creada: ' . $table);
    }
}
echo "Esquema SQL: OK\n";

// Simula la base existente anterior a la migración en esta BD efímera.
$conn->query('ALTER TABLE recetas DROP FOREIGN KEY fk_recetas_usuario');
$conn->query('ALTER TABLE recetas DROP COLUMN usuario_id');

$migration = file_get_contents(__DIR__ . '/../database/05_migracion_propietarios.sql');
if ($migration === false) {
    throw new RuntimeException('No se pudo leer la migración de propietarios.');
}
$migration = str_replace('reduccion_desperdicio_portafolio', 'reduccion_desperdicio_ci', $migration);

// Se prueba la migración dos veces para comprobar que no duplica columnas.
for ($attempt = 0; $attempt < 2; $attempt++) {
    $conn->multi_query($migration);
    do {
        $result = $conn->store_result();
        if ($result instanceof mysqli_result) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
}
$check = $conn->query("SHOW COLUMNS FROM recetas LIKE 'usuario_id'");
if ($check->num_rows !== 1) {
    throw new RuntimeException('La migración no agregó usuario_id.');
}
echo "Migración de propietarios repetible: OK\n";


$email = 'smoke-test@example.invalid';
$hash = password_hash('PruebaSegura2026!', PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)');
$name = 'Usuario de prueba';
$stmt->bind_param('sss', $name, $email, $hash);
$stmt->execute();
$userId = $conn->insert_id;
$stmt->close();

$stmt = $conn->prepare('SELECT password FROM usuarios WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$row || !password_verify('PruebaSegura2026!', $row['password'])) {
    throw new RuntimeException('Autenticación de prueba fallida.');
}
echo "Usuarios + hash: OK\n";

$stmt = $conn->prepare('INSERT INTO recetas (usuario_id, nombre, ingredientes, instrucciones) VALUES (?, ?, ?, ?)');
$recipe = 'Tortilla CI';
$ingredients = 'Huevo, cebolla';
$instructions = 'Cocinar bien todos los ingredientes.';
$stmt->bind_param('isss', $userId, $recipe, $ingredients, $instructions);
$stmt->execute();
$recipeId = $conn->insert_id;
$stmt->close();
// Verifica integridad referencial y atribución del autor.
$stmt = $conn->prepare('SELECT usuario_id FROM recetas WHERE id = ?');
$stmt->bind_param('i', $recipeId);
$stmt->execute();
$owner = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$owner || (int) $owner['usuario_id'] !== (int) $userId) {
    throw new RuntimeException('No se asignó el propietario a la receta.');
}
$stmt = $conn->prepare('SELECT id FROM recetas WHERE ingredientes LIKE ?');
$filter = '%Huevo%';
$stmt->bind_param('s', $filter);
$stmt->execute();
if ($stmt->get_result()->num_rows < 1) {
    throw new RuntimeException('Búsqueda de recetas fallida.');
}
$stmt->close();
echo "Recetas + búsqueda: OK\n";

$stmt = $conn->prepare('INSERT INTO donaciones (nombre_donante, alimento, cantidad, fecha, punto_recoleccion) VALUES (?, ?, ?, ?, ?)');
$donor = 'Donante CI';
$food = 'Arroz';
$amount = 2;
$date = date('Y-m-d');
$pickup = 'Punto de prueba';
$stmt->bind_param('ssiss', $donor, $food, $amount, $date, $pickup);
$stmt->execute();
if ($stmt->affected_rows !== 1) {
    throw new RuntimeException('Donación de prueba fallida.');
}
$stmt->close();
echo "Donaciones: OK\n";

$conn->query('DELETE FROM donaciones WHERE nombre_donante = "Donante CI"');
$conn->query('DELETE FROM recetas WHERE id = ' . (int) $recipeId);
$conn->query('DELETE FROM usuarios WHERE id = ' . (int) $userId);
echo "Prueba de integración completada.\n";
