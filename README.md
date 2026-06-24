# API de gestión de pedidos

API REST desarrollada con Laravel 10 para gestionar usuarios, productos y pedidos.

La aplicación permite registrar e iniciar sesión con usuarios, crear pedidos con varios productos, consultar los pedidos del usuario autenticado y cancelar pedidos pendientes.

## Tecnologías utilizadas

* PHP 8.2
* Laravel 10
* Laravel Sanctum
* MySQL o MariaDB
* PHPUnit

## Requisitos

Antes de instalar el proyecto es necesario disponer de:

* PHP 8.1 o superior
* Composer
* MySQL o MariaDB
* Git

## Instalación

Clonar el repositorio:

```bash
git clone https://github.com/ivanMR95/prueba-tecnica-pedidos.git
```

Entrar en la carpeta del proyecto:

```bash
cd prueba-tecnica-pedidos
```

Instalar las dependencias:

```bash
composer install
```

Crear el archivo de configuración del entorno.

En Linux o macOS:

```bash
cp .env.example .env
```

En Windows:

```bash
copy .env.example .env
```

Generar la clave de la aplicación:

```bash
php artisan key:generate
```

## Configuración de la base de datos

Crear una base de datos vacía y configurar las siguientes variables en el archivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prueba_tecnica_pedidos
DB_USERNAME=root
DB_PASSWORD=
```

Los valores deben modificarse según la configuración local de MySQL o MariaDB.

Ejecutar las migraciones y cargar los productos de prueba:

```bash
php artisan migrate --seed
```

El seeder crea diez productos con diferentes precios y cantidades de stock.

## Iniciar el servidor

```bash
php artisan serve
```

La API estará disponible en:

```text
http://127.0.0.1:8000
```

## Autenticación

La API utiliza Laravel Sanctum.

Los endpoints protegidos requieren enviar el token en la cabecera:

```text
Authorization: Bearer TOKEN
```

También se recomienda enviar las siguientes cabeceras:

```text
Accept: application/json
Content-Type: application/json
```

## Endpoints

### Registrar usuario

```http
POST /api/register
```

Ejemplo de petición:

```json
{
    "name": "Ivan Martinez",
    "email": "ivan@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

La respuesta incluye el usuario registrado y un token de acceso.

### Iniciar sesión

```http
POST /api/login
```

Ejemplo de petición:

```json
{
    "email": "ivan@example.com",
    "password": "password123"
}
```

La respuesta incluye un nuevo token de acceso.

### Crear un pedido

```http
POST /api/orders
```

Requiere autenticación.

Ejemplo de petición:

```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        },
        {
            "product_id": 3,
            "quantity": 1
        }
    ]
}
```

Al crear el pedido:

* Se comprueba que los productos existan.
* Se valida que exista stock suficiente.
* El precio unitario se copia desde el precio actual del producto.
* El subtotal se calcula usando el precio y la cantidad.
* El total del pedido se calcula desde sus líneas.
* El stock de los productos se descuenta automáticamente.

### Listar pedidos

```http
GET /api/orders
```

Requiere autenticación.

Devuelve únicamente los pedidos pertenecientes al usuario autenticado.

### Consultar un pedido

```http
GET /api/orders/{order}
```

Requiere autenticación.

Devuelve el pedido junto con sus líneas y los productos relacionados.

Un usuario no puede consultar pedidos pertenecientes a otro usuario.

### Cancelar un pedido

```http
PUT /api/orders/{order}/cancel
```

Requiere autenticación.

Solo se pueden cancelar pedidos con estado `pending`.

Un pedido con estado `completed` o `cancelled` no puede volver a cancelarse.

## Estados de un pedido

Los pedidos pueden tener los siguientes estados:

```text
pending
completed
cancelled
```

Los pedidos nuevos se crean con estado `pending`.

## Respuestas HTTP principales

* `200 OK`: operación realizada correctamente.
* `201 Created`: usuario o pedido creado correctamente.
* `401 Unauthorized`: usuario no autenticado.
* `403 Forbidden`: el usuario no es propietario del pedido.
* `404 Not Found`: recurso no encontrado.
* `422 Unprocessable Entity`: error de validación, stock insuficiente o estado no permitido.

## Ejecución de tests

Para ejecutar todos los tests:

```bash
php artisan test
```

Los tests incluidos comprueban:

* Que un usuario autenticado puede crear un pedido.
* Que el total del pedido se calcula correctamente.
* Que los precios de los productos se copian a las líneas.
* Que el stock se descuenta.
* Que un usuario no puede consultar pedidos de otro usuario.

## Decisiones técnicas

### Transacciones

La creación del pedido se realiza dentro de una transacción de base de datos.

Si falla la creación de una línea o el descuento del stock, todos los cambios se revierten y no queda un pedido incompleto.

### Bloqueo de productos

Los productos se consultan utilizando `lockForUpdate()` durante la creación del pedido.

Esto reduce el riesgo de que dos peticiones simultáneas utilicen el mismo stock disponible.

### Observer

Se utiliza un `OrderItemObserver` para recalcular el total del pedido cuando una línea se crea, modifica o elimina.

El total no se recibe desde el cliente, sino que se obtiene sumando los subtotales de sus líneas.

### Evento y listener

Después de crear el pedido se dispara el evento `OrderCreated`.

El listener `DecreaseProductStock` comprueba y descuenta el stock de los productos.

El listener se ejecuta de forma síncrona para que cualquier error pueda provocar la reversión de la transacción.

### Middleware

El middleware `CheckOrderOwner` comprueba que el usuario autenticado sea el propietario del pedido antes de permitir consultar su detalle o cancelarlo.

### Form Requests

Las peticiones de registro, inicio de sesión y creación de pedidos utilizan Form Requests para separar las validaciones de los controladores.

### API Resources

Las respuestas de pedidos y líneas se generan mediante API Resources para controlar los datos expuestos por la API.

### Eliminación de registros relacionados

* Un usuario con pedidos asociados no puede eliminarse.
* Al eliminar un pedido se eliminan sus líneas.
* Un producto utilizado en un pedido no puede eliminarse.

Estas restricciones permiten conservar el historial de los pedidos.

## Datos de prueba

Los productos pueden volver a cargarse ejecutando:

```bash
php artisan db:seed --class=ProductSeeder
```

Para reconstruir completamente la base de datos durante el desarrollo:

```bash
php artisan migrate:fresh --seed
```

Este último comando elimina todos los datos existentes antes de ejecutar nuevamente las migraciones y los seeders.
