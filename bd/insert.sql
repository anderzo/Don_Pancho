
INSERT INTO sucursales ( nombre, direccion) VALUES
('Sucursal Toncontín', 'Tegucigalpa, Toncontín'),
( 'Sucursal Suyapa', 'Tegucigalpa, Blvd. Suyapa'),
( 'Sucursal Medina', 'San Pedro Sula, Barrio Medina'),
('Sucursal La Ceiba', 'La Ceiba, Carretera CA-13');


-- La contraseña '123456' 
INSERT INTO usuarios (nombre, usuario, password, telefono, rol, estado, sucursal_id) VALUES
('Carlos Pérez', 'admin_donpancho', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+504 9876-5432', 'admin', 'activo', 1),
('Juan Bodeguero', 'bodega_sps', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+504 3322-1144', 'bodeguero', 'activo', 3);

INSERT INTO categorias (id, nombre) VALUES
('Frenos y Suspensión'),
('Motor y Transmisión'),
('Sistema Eléctrico'),
('Fluidos y Lubricantes');

INSERT INTO repuestos ( nombre, descripcion, precio, stock, fotografia, insignia) VALUES
('Pastillas de Freno Cerámicas delanteras', 'Juego de pastillas cerámicas alta durabilidad para vehículos japoneses y coreanos.', 850.00, 25, 'pastillas_freno.jpg', 'Destacado'),
('Amortiguador Delantero Gas', 'Amortiguador hidráulico de gas para mayor estabilidad en carretera.', 1250.00, 12, 'amortiguador_delantero.jpg', 'NoDestacado'),
('Discos de Freno Ventilados', 'Par de discos de freno delanteros ventilados resistentes a altas temperaturas.', 1400.00, 8, 'discos_freno.jpg', 'Destacado');

-- Categoría 2: Motor y Transmisión
INSERT INTO repuestos ( nombre, descripcion, precio, stock, fotografia, insignia) VALUES
('Kit de Embrague (Clutch)', 'Incluye plato, disco y balinera para vehículos sedán y pick-up.', 3200.00, 6, 'kit_embrague.jpg', 'Destacado'),
('Faja de Distribución', 'Faja de alta resistencia para sincronización de motor.', 450.00, 30, 'faja_distribucion.jpg', 'NoDestacado'),
('Bomba de Agua', 'Bomba de refrigerante de alta eficiencia con empaque incluido.', 980.00, 14, 'bomba_agua.jpg', 'NoDestacado');

-- Categoría 3: Sistema Eléctrico
INSERT INTO repuestos (nombre, descripcion, precio, stock, fotografia, insignia) VALUES
('Bujías de Iridium (Paquete x4)', 'Bujías de encendido de alto rendimiento y larga duración.', 650.00, 40, 'bujias_iridium.jpg', 'Destacado'),
('Alternador Remanufacturado', 'Alternador de 12V con regulador integrado para carga óptima.', 2800.00, 5, 'alternador.jpg', 'NoDestacado'),
('Motor de Arranque (Starter)', 'Motor de arranque para encendido rápido en frío.', 3100.00, 4, 'motor_arranque.jpg', 'NoDestacado');

-- Categoría 4: Fluidos y Lubricantes
INSERT INTO repuestos ( nombre, descripcion, precio, stock, fotografia, insignia) VALUES
('Aceite Sintético 5W-30 (1 Galón)', 'Aceite de motor totalmente sintético para máxima protección contra el desgaste.', 950.00, 50, 'aceite_5w30.jpg', 'Destacado'),
('Líquido de Frenos DOT 4', 'Líquido de frenos de alto punto de ebullición (500 ml).', 180.00, 35, 'liquido_frenos.jpg', 'NoDestacado'),
('Refrigerante de Motor Prediluido', 'Anticongelante y refrigerante listo para usar (1 Galón).', 260.00, 25, 'refrigerante.jpg', 'NoDestacado');

INSERT INTO kits_tune_up (marca, viscosidad_aceite, repuestos_adicionales, costo_extra) VALUES
('Sedán', 'Toyota', '5W-30', 'Bujías de iridio, pastillas cerámicas, limpiador de inyectores', 1250.00),
('SUV', 'Honda', '10W-30', 'Filtro de aire, líquido de frenos, refrigerante', 980.00),
('Pick-Up 4x4', 'Nissan', '15W-40', 'Filtro de combustible, pastillas de freno, refrigerante', 1450.00);