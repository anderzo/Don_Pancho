<?php
    declare(strict_types=1);

     if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    class ConexionBD {
        private string $host = "localhost";
        private string $name = "don_pancho";
        private string $user = "root";
        private string $pass = "";
        private string $charset = "utf8mb4";

        public function conectar(): ?PDO {
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset={$this->charset}";
            
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
                PDO::ATTR_EMULATE_PREPARES   => false,                 
            ];

            try {
                $pdo = new PDO($dsn, $this->user, $this->pass, $opciones);
                return $pdo;
            } catch (PDOException $e) {
                error_log("Error en conexión BD: " . $e->getMessage());
            
                echo "<div style='color:red; background:#fee2e2; padding:15px; border-radius:8px;'>
                        <strong> Error de Conexión:</strong> No se pudo conectar a la base de datos de Don Pancho. Verifique que MySQL esté iniciado en XAMPP.
                    </div>";
                return null;
            }
        }
    }
?>