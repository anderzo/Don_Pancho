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

        if ($subtotal <= 0 || $cantidad <= 0) {
            throw new InvalidArgumentException(
                "El subtotal y la cantidad deben ser mayores que cero."
            );
        }

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

    // Validar teléfono de Honduras
    public function validarTelefono(string $telefono): bool {
        return preg_match('/^[2389][0-9]{7}$/', $telefono) === 1;
    }

    // Guardar una factura completa
    public function guardarFactura(
        string $rtn,
        string $telefono,
        int $sucursalId,
        string $tipoFlete,
        array $productos
    ): string {

        if (!$this->validarRTN($rtn)) {
            throw new InvalidArgumentException("El RTN debe tener 14 dígitos.");
        }

        if (!$this->validarTelefono($telefono)) {
            throw new InvalidArgumentException("El teléfono no es válido.");
        }

        if (empty($productos)) {
            throw new InvalidArgumentException("Debe seleccionar al menos un repuesto.");
        }

        try {
            $this->pdo->beginTransaction();

            // Verificar que la sucursal exista
            $stmt = $this->pdo->prepare(
                "SELECT id FROM sucursales WHERE id = ?"
            );
            $stmt->execute([$sucursalId]);

            if (!$stmt->fetch()) {
                throw new InvalidArgumentException("La sucursal seleccionada no existe.");
            }

            $subtotal = 0;
            $cantidadTotal = 0;
            $detalles = [];
            $repuestosProcesados = [];

            // Consultar precios y existencias reales
            $consulta = $this->pdo->prepare(
                "SELECT id, nombre, precio, stock
                 FROM repuestos
                 WHERE id = ?
                 FOR UPDATE"
            );

            foreach ($productos as $producto) {

                $repuestoId = (int)($producto['id'] ?? 0);
                $cantidad = (int)($producto['cantidad'] ?? 0);

                if ($repuestoId <= 0 || $cantidad <= 0) {
                    throw new InvalidArgumentException("Producto o cantidad inválida.");
                }

                // Evitar que un repuesto aparezca repetido
                if (isset($repuestosProcesados[$repuestoId])) {
                    throw new InvalidArgumentException(
                        "No se puede repetir el mismo repuesto."
                    );
                }

                $repuestosProcesados[$repuestoId] = true;

                $consulta->execute([$repuestoId]);
                $repuesto = $consulta->fetch();

                if (!$repuesto) {
                    throw new InvalidArgumentException("El repuesto no existe.");
                }

                if ($cantidad > (int)$repuesto['stock']) {
                    throw new InvalidArgumentException(
                        "No hay suficiente existencia de " . $repuesto['nombre']
                    );
                }

                $precio = (float)$repuesto['precio'];
                $subtotalLinea = $precio * $cantidad;

                $subtotal += $subtotalLinea;
                $cantidadTotal += $cantidad;

                $detalles[] = [
                    'id' => $repuestoId,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $subtotalLinea
                ];
            }

            // Calcular descuento, ISV y flete
            $totales = $this->calcularTotales(
                $subtotal,
                $cantidadTotal,
                $tipoFlete
            );

            // Guardar factura principal
            $sqlFactura = "INSERT INTO facturas
                (numero_factura, rtn, telefono, tipo_flete,
                 sucursal_id, subtotal, descuento, isv, total)
                VALUES ('PENDIENTE', ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmtFactura = $this->pdo->prepare($sqlFactura);

            $stmtFactura->execute([
                $rtn,
                $telefono,
                $tipoFlete,
                $sucursalId,
                $totales['subtotal'],
                $totales['descuento'],
                $totales['isv'],
                $totales['total']
            ]);

            $facturaId = (int)$this->pdo->lastInsertId();

            // Generar número de factura usando el ID
            $numeroFactura = 'DP-' . str_pad(
                (string)$facturaId,
                8,
                '0',
                STR_PAD_LEFT
            );

            $stmtNumero = $this->pdo->prepare(
                "UPDATE facturas SET numero_factura = ? WHERE id = ?"
            );

            $stmtNumero->execute([$numeroFactura, $facturaId]);

            // Guardar detalles y descontar inventario
            $sqlDetalle = "INSERT INTO detalle_factura
                (factura_id, repuesto_id, cantidad, precio_unitario, subtotal_linea)
                VALUES (?, ?, ?, ?, ?)";

            $stmtDetalle = $this->pdo->prepare($sqlDetalle);

            $sqlStock = "UPDATE repuestos
                         SET stock = stock - ?
                         WHERE id = ? AND stock >= ?";

            $stmtStock = $this->pdo->prepare($sqlStock);

            foreach ($detalles as $detalle) {

                $stmtDetalle->execute([
                    $facturaId,
                    $detalle['id'],
                    $detalle['cantidad'],
                    $detalle['precio'],
                    $detalle['subtotal']
                ]);

                $stmtStock->execute([
                    $detalle['cantidad'],
                    $detalle['id'],
                    $detalle['cantidad']
                ]);

                if ($stmtStock->rowCount() === 0) {
                    throw new RuntimeException(
                        "No fue posible actualizar el inventario."
                    );
                }
            }

            // Confirmar todos los cambios
            $this->pdo->commit();

            return $numeroFactura;

        } catch (Throwable $e) {

            // Deshacer cambios si ocurre algún error
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

}

