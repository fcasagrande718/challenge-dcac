<?php

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host = getenv('DB_HOST') ?: 'localhost';
            $db = getenv('DB_NAME') ?: 'productos';
            $user = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: '';

            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $db);

            try {
                self::$connection = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $exception) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'No se pudo conectar a la base de datos', 'details' => $exception->getMessage()]);
                exit;
            }
        }

        return self::$connection;
    }
}
