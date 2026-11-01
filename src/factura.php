<?php
declare(strict_types=1);

class Factura {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Consultar los repuestos disponibles
    public function listarRepuestos(): array {
        $sql = "SELECT id, nombre, precio, stock
                FROM repuestos
                WHERE stock > 0
                ORDER BY nombre";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Consultar las sucursales
    public function listarSucursales(): array {
        $sql = "SELECT id, nombre, direccion
                FROM sucursales
                ORDER BY nombre";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Calcular subtotal, descuento, ISV y flete
    public function calcularTotales(
        float $subtotal,
        int $cantidad,
        string $tipoFlete
    ): array {

        $descuento = 0;

        if ($cantidad >= 10) {
            $descuento = $subtotal * 0.10;
        }

        $baseImponible = $subtotal - $descuento;

        $isv = $baseImponible * 0.15;

        $fletes = [
            'retiro' => 0,
            'moto_express' => 50,
            'camionsito' => 180
        ];

        if (!isset($fletes[$tipoFlete])) {
            throw new InvalidArgumentException("Tipo de flete inválido.");
        }

        $costoFlete = $fletes[$tipoFlete];

        $total = $baseImponible + $isv + $costoFlete;

        return [
            'subtotal' => round($subtotal, 2),
            'descuento' => round($descuento, 2),
            'isv' => round($isv, 2),
            'flete' => $costoFlete,
            'total' => round($total, 2)
        ];
    }

    // Validar RTN de Honduras
    public function validarRTN(string $rtn): bool {
        return preg_match('/^[0-9]{14}$/', $rtn) === 1;
    }

    // Validar telefono de Honduras
    public function validarTelefono(string $telefono): bool {
        return preg_match('/^[2389][0-9]{7}$/', $telefono) === 1;
    }

}
