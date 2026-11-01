CREATE DATABASE IF NOT EXISTS Don_Pancho;
USE Don_Pancho;

CREATE USER IF NOT EXISTS 'GrupoProgra'@'localhost' IDENTIFIED BY 'PrograWeb123';
GRANT ALL ON Don_Pancho.* TO 'GrupoProgra'@'localhost';
FLUSH PRIVILEGES;

create table categorias (
    id int auto_increment primary key,
    nombre varchar(100) not null
);

create table repuestos (
    id int auto_increment primary key,
    categoria_id int not null,
    nombre varchar(150) not null,
    descripcion text,
    precio decimal(10, 2) not null,
    stock int not null default 0,
    fotografia varchar(255) not null,
    insignia enum('Destacado', 'NoDestacado') not null default 'NoDestacado',
    constraint fk_repuestos_categoria foreign key (categoria_id) references categorias(id) on delete restrict on update cascade
);

create table sucursales (
    id int auto_increment primary key,
    nombre varchar(100) not null,
    direccion varchar(255) not null
);

create table usuarios (
    id int auto_increment primary key,
    nombre varchar(100) not null,
    usuario varchar(50) not null,
    password varchar(255) not null,
    telefono varchar(20) not null,
    rol enum('usuario', 'admin', 'bodeguero') not null default 'usuario',
    estado enum('activo', 'inactivo') not null default 'activo',
    sucursal_id int null,
    constraint uq_usuarios_usuario unique (usuario),
    constraint fk_usuarios_sucursal foreign key (sucursal_id) references sucursales(id) on delete set null on update cascade
);

create table facturas (
    id int auto_increment primary key,
    numero_factura varchar(50) not null,
    rtn varchar(20) not null,
    telefono varchar(20) not null,
    tipo_flete enum('retiro', 'moto_express', 'camionsito') not null,
    sucursal_id int not null,
    subtotal decimal(10, 2) not null,
    descuento decimal(10, 2) default 0.00,
    isv decimal(10, 2) not null,
    total decimal(10, 2) not null,
    fecha timestamp default current_timestamp,
    constraint uq_facturas_numero unique (numero_factura),
    constraint fk_facturas_sucursal foreign key (sucursal_id) references sucursales(id) on delete restrict on update cascade
);

create table detalle_factura (
    id int auto_increment primary key,
    factura_id int not null,
    repuesto_id int not null,
    cantidad int not null,
    precio_unitario decimal(10, 2) not null,
    subtotal_linea decimal(10, 2) not null,
    constraint fk_detalle_factura_factura foreign key (factura_id) references facturas(id) on delete cascade on update cascade,
    constraint fk_detalle_factura_repuesto foreign key (repuesto_id) references repuestos(id) on delete restrict on update cascade
);

create table kits_tune_up (
    id int auto_increment primary key,
    tipo_vehiculo varchar(50) not null,
    marca varchar(50) not null,
    viscosidad_aceite varchar(50) not null,
    repuestos_adicionales text,
    costo_extra decimal(10, 2)
);