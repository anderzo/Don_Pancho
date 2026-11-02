# Autopartes & Repuestos "Don Pancho"

Plataforma web para una empresa hondureña que vende repuestos para vehículos japoneses, coreanos y americanos, con sucursales en Tegucigalpa, San Pedro Sula y La Ceiba.

El sistema tiene **dos partes**:

1. **Portal de Clientes y Talleres:** donde los clientes ven el catálogo, arman su kit de mantenimiento y compran con factura.
2. **Panel Administrativo y Bodega:** zona privada (con usuario y contraseña) donde se administra el inventario de repuestos.

---

## Integrantes
Integrantes del Grupo

- Nelson Alexander Iraheta Rosales - 20221030320
- Flor Angel Arteaga Montes - 20211000147
- Jenifer Ondina Barrantes Martínez - 20161000040
- Francisco Emanuel Cruz Gómez - 20201002174
- Richard Anderzon Garcia Rivera - 20171001462

**Repositorio:** https://github.com/anderzo/Don_Pancho

---

## ¿Qué puede hacer el sistema?

### Portal de Clientes
- **Página de inicio** con banner "Expertos en Repuestos Genuinos y Compatibles", 5 repuestos destacados, marcas aliadas (Toyota, Nissan, Honda, Hyundai, Isuzu, Ford) y pie de página.
- **Catálogo** con fotos, detalles y precios, ordenado por categoría: Frenos y Suspensión, Motor y Transmisión, Sistema Eléctrico, Fluidos y Lubricantes. Todo sale de la base de datos.
- **Arma tu Kit Tune-Up:** el cliente elige tipo de vehículo (Sedán, SUV o Pick-Up 4x4), marca y viscosidad del aceite, filtros y extras con costo en Lempiras (bujías de iridio, pastillas cerámicas, limpiador de inyectores, líquido de frenos, refrigerante).
- **Facturación SAR:** formulario con validación de RTN y teléfono, descuento automático, 15% de ISV y número de factura correlativo guardado en la base de datos.
- **Sucursales:** lista de las sucursales (Toncontín, Suyapa, Medina y CA-13) y el historial de compras.

### Panel Administrativo y Bodega
- Inicio y cierre de sesión.
- Solo entran usuarios con rol **admin**, **bodeguero** o **usuario**. Si alguien intenta entrar sin permiso, lo manda a `login.php`.
- El menú superior cambia: muestra **Iniciar Sesión** a los visitantes, o el nombre y rol del usuario con el botón **Cerrar Sesión**.
- Control del inventario (crear, ver, editar y eliminar) todo con **ventanas emergentes (modales)**, sin cambiar de página:
  - Tabla de inventario
  - Ventana para registrar
  - Ventana para editar
  - Ventana para confirmar la eliminación

### Subida segura de fotos (4 candados)
1. Se revisa que el archivo llegó sin errores.
2. Tamaño máximo de **4 MB**.
3. Solo se aceptan **jpg, jpeg, png y webp** (se revisa la extensión y el tipo real del archivo).
4. Se le cambia el nombre por uno único y seguro, se guarda en la carpeta `img/` y el nombre queda en la base de datos.

---

## Requisitos

- **XAMPP** 
- **PHP** 8.0 o superior
- **MySQL** o MariaDB

---

## Cómo instalar y probar el proyecto

1. Instala **XAMPP** y abre el panel de control.
2. Inicia los módulos **Apache** y **MySQL**.
3. Copia la carpeta del proyecto dentro de `C:\xampp\htdocs\`.
4. Abre **phpMyAdmin**: http://localhost/phpmyadmin
5. En phpMyAdmin, ve a la pestaña **Importar** y carga los archivos **en este orden**:
   1. `bd.sql`: crea la base de datos `don_pancho`, las tablas y el usuario de la BD.
   2. `insert.sql`: agrega los datos de prueba.
6. Abre el proyecto en el navegador
7. Para probar, inicia sesión con un usuario de prueba (admin o bodeguero) y revisa el catálogo, repuestos, sucursales y facturación.

### Credenciales de la base de datos

| Dato | Valor |
|------|-------|
| Base de datos | Don_Pancho |
| Usuario | GrupoProgra |
| Contraseña | PrograWeb123 |

---

## Credenciales para evaluar el sistema

### Usuarios de prueba

| Rol | Usuario | Contraseña |
|-----|---------|------------|
| Admin | admin_donpancho | 123456 |
| Bodeguero | bodega_sps | 123456 |

### Base de datos

| Dato | Valor |
|------|-------|
| Servidor | `localhost` |
| Nombre de la base | `don_pancho` |
| Usuario | `GrupoProgra` |
| Contraseña | `PrograWeb123` |

---

## Estructura del proyecto

```
Don_Pancho/
├── bd/     → scripts SQL
├── css/    → estilos
├── img/    → imágenes
├── src/    → código PHP (index.php, catálogo, repuestos, etc.)
└── .gitignore
```
---

## Tecnologías usadas
- PHP 
- MySQL con PDO
- HTML y CSS
- Git y GitHub

---
## Control de versiones

El proyecto se desarrolló con **Git**, haciendo commits pequeños y progresivos (un cambio por commit). El archivo `.gitignore` evita subir archivos que no hacen falta.

---
