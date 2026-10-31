<?php
declare(strict_types=1);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}
$ingrediente = trim((string) ($_POST['ingrediente'] ?? ''));
if ($ingrediente === '' || strlen($ingrediente) > 180) {
    http_response_code(422);
    exit('<p>Ingrese un ingrediente válido.</p>');
}
require_once __DIR__ . '/db_config.php';

try {
    $stmt = $conn->prepare('SELECT id, nombre, instrucciones FROM recetas WHERE ingredientes LIKE ? ORDER BY id DESC LIMIT 50');
    $search = '%' . $ingrediente . '%';
    $stmt->bind_param('s', $search);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo '<p>No se encontraron recetas con ese ingrediente.</p>';
    } else {
        echo '<h2>Recetas encontradas</h2>';
        while ($row = $result->fetch_assoc()) {
            $id = (int) $row['id'];
            echo '<article class="receta">';
            echo '<h3><a href="recetas.php?id=' . $id . '">' . htmlspecialchars((string) $row['nombre'], ENT_QUOTES, 'UTF-8') . '</a></h3>';
            echo '<p>' . nl2br(htmlspecialchars((string) $row['instrucciones'], ENT_QUOTES, 'UTF-8')) . '</p>';
            echo '</article>';
        }
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log('Error al buscar recetas: ' . $e->getMessage());
    http_response_code(500);
    echo '<p>No se pudo completar la búsqueda.</p>';
}
