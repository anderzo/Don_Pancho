<?php
declare(strict_types=1);
require __DIR__ . '/conexiones/conexionPDO.php';
class Repuesto{
    private PDO $pdo;
    public function __construct(PDO $pdo){
        $this->pdo =$pdo;
    
    }
    public function listar():array{
        $sql = "SELECT id, categoria_id, nombre, descripcion, precio, stock, fotografia, insignia  FROM repuestos";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminar(int $id):bool{
        $sql = "DELETE FROM repuestos WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id'=>$id]);
    }

    public function insertar(int $categoria_id, string $nombre, string $descripcion, float $precio, int $stock, string $fotografia, string $insignia): int{
        $sql = "INSERT INTO repuestos(categoria_id, nombre, descripcion, precio, stock, fotografia, insignia)
        VALUES (:categoria_id,:nombre,:descripcion,:precio,:stock,:fotografia,:insignia)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':categoria_id'=>$categoria_id,
            ':nombre'=>$nombre,
            ':descripcion'=>$descripcion,
            ':precio'=>$precio,
            ':stock'=>$stock,
            ':fotografia'=>$fotografia,
            ':insignia'=>$insignia
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar(int $id, float $nuevoPrecio, int $nuevoStock, string $nuevaInsignia): bool {
        $sql = "UPDATE repuestos 
                SET precio = :prec, stock = :stk, insignia = :nuevaInsignia
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':prec' => $nuevoPrecio,
            ':stk'  => $nuevoStock,
            ':nuevaInsignia'=> $nuevaInsignia,
            ':id'   => $id
        ]);
    }
}
?>