<?php
declare(strict_types=1);

/**
 * Límite de solicitudes por IP, usando archivos de corta duración fuera del webroot.
 * Adecuado para pruebas y despliegues pequeños de un solo servidor. Para varios
 * servidores o tráfico elevado usar Redis/WAF en un entorno de producción.
 *
 * No confía en X-Forwarded-For suministrado por el cliente.
 */
function ambienteAllowRequest(string $action, int $maxAttempts, int $windowSeconds): bool
{
    if ($maxAttempts < 1 || $windowSeconds < 1
        || preg_match('/^[a-z0-9_-]{1,32}$/D', $action) !== 1) {
        return false;
    }

    $root = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR . 'ambientewebcs-limits';
    if (!is_dir($root) && !@mkdir($root, 0700, true) && !is_dir($root)) {
        error_log('No se pudo crear el directorio de límites de solicitudes.');
        return false;
    }

    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'cli');
    $key = hash('sha256', __DIR__ . '|' . $action . '|' . $ip);
    $file = $root . DIRECTORY_SEPARATOR . $key . '.json';
    $handle = @fopen($file, 'c+');
    if ($handle === false) {
        error_log('No se pudo abrir el registro de límites de solicitudes.');
        return false;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return false;
        }
        rewind($handle);
        $stored = stream_get_contents($handle);
        $timestamps = json_decode((string) $stored, true);
        if (!is_array($timestamps)) {
            $timestamps = [];
        }
        $now = time();
        $recent = array_values(array_filter($timestamps, static fn($t) =>
            is_int($t) && $t > $now - $windowSeconds && $t <= $now
        ));

        if (count($recent) >= $maxAttempts) {
            return false;
        }
        $recent[] = $now;
        rewind($handle);
        if (!ftruncate($handle, 0)
            || fwrite($handle, json_encode($recent, JSON_THROW_ON_ERROR)) === false) {
            return false;
        }
        fflush($handle);
        return true;
    } catch (Throwable $e) {
        error_log('Falló el límite de solicitudes.');
        return false;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}
