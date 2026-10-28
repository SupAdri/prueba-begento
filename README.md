# API REST de productos, divisas y precios

## 1. Descripción

API REST desarrollada con Laravel para gestionar productos y sus precios en distintas divisas.

La API permite:

- Crear productos con su precio base.
- Consultar todos los productos o uno específico.
- Actualizar los datos de un producto.
- Eliminar productos.
- Consultar los precios de un producto en distintas divisas.
- Agregar precios adicionales en otras divisas.
- Sincronizar el precio base del producto con su registro en la tabla `product_prices`.

## 2. Instalación y ejecución

Configura la conexión a la base de datos en el archivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prueba_begento
DB_USERNAME=root
DB_PASSWORD=
```

Instala las dependencias:

```bash
composer install
```

Ejecuta las migraciones:

```bash
php artisan migrate
```

Inicia el servidor:

```bash
php artisan serve
```

La URL base será:

```text
http://127.0.0.1:8000/api
```

## 3. Formato de las peticiones

Las peticiones que envían datos deben utilizar estas cabeceras:

```http
Accept: application/json
Content-Type: application/json
```

Los datos se envían en formato JSON.

## 4. Modelo de datos

### Productos (`products`)

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | Integer | Identificador del producto |
| `name` | String | Nombre |
| `description` | String / null | Descripción |
| `price` | Decimal | Precio base |
| `currency_id` | Integer | Identificador de la divisa base |
| `tax_cost` | Decimal | Coste de impuestos |
| `manufacturing_cost` | Decimal | Coste de fabricación |

### Divisas (`currencies`)

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | Integer | Identificador de la divisa |
| `name` | String | Nombre o código, por ejemplo `USD` |
| `symbol` | String | Símbolo monetario |
| `exchange_rate` | Decimal | Tipo de cambio configurado |

### Precios (`product_prices`)

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | Integer | Identificador del precio |
| `product_id` | Integer | Producto relacionado |
| `currency_id` | Integer | Divisa del precio |
| `price` | Decimal | Precio expresado en esa divisa |

La API recibe el campo `currency` como nombre de la divisa, por ejemplo `USD`, y busca su ID internamente.

## 5. Resumen de endpoints

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/products` | Listar productos |
| POST | `/products` | Crear producto |
| GET | `/products/{id}` | Consultar un producto |
| PUT | `/products/{id}` | Actualizar producto |
| PATCH | `/products/{id}` | Actualizar parcialmente, según la configuración de rutas |
| DELETE | `/products/{id}` | Eliminar producto |
| GET | `/products/{id}/prices` | Consultar precios |
| POST | `/products/{id}/prices` | Agregar un precio |

Los ejemplos siguientes utilizan datos ilustrativos.

---

## 6. Listar productos

**Método:** `GET`

**Endpoint:**

```http
GET /api/products
```

No requiere cuerpo en la petición.

### Respuesta exitosa — `200 OK`

```json
[
    {
        "id": 1,
        "name": "MacBook Pro M3",
        "description": "Portátil Apple",
        "price": "1499.99",
        "currency_id": 1,
        "tax_cost": "150.00",
        "manufacturing_cost": "900.00",
        "currency": {
            "id": 1,
            "name": "USD",
            "symbol": "$",
            "exchange_rate": "1.00000000"
        },
        "prices": [
            {
                "id": 1,
                "product_id": 1,
                "currency_id": 1,
                "price": "1499.99",
                "currency": {
                    "id": 1,
                    "name": "USD",
                    "symbol": "$",
                    "exchange_rate": "1.00000000"
                }
            }
        ]
    }
]
```

La respuesta contiene los productos, su divisa base y los precios asociados.

Los campos adicionales de fechas dependen de la serialización y configuración de los modelos.

---

## 7. Crear un producto

**Método:** `POST`

**Endpoint:**

```http
POST /api/products
```

### Envío de datos

```json
{
    "name": "MacBook Pro M3",
    "description": "Portátil Apple",
    "price": 1499.99,
    "currency": "USD",
    "tax_cost": 150,
    "manufacturing_cost": 900
}
```

### Validaciones

| Campo | Regla |
|---|---|
| `name` | Obligatorio, texto de máximo 255 caracteres |
| `description` | Opcional, texto o null |
| `price` | Obligatorio, numérico y mayor o igual que cero |
| `currency` | Obligatorio y debe existir en `currencies.name` |
| `tax_cost` | Obligatorio, numérico y mayor o igual que cero |
| `manufacturing_cost` | Obligatorio, numérico y mayor o igual que cero |

### Respuesta exitosa — `201 Created`

```json
{
    "id": 1,
    "name": "MacBook Pro M3",
    "description": "Portátil Apple",
    "price": "1499.99",
    "currency_id": 1,
    "tax_cost": "150.00",
    "manufacturing_cost": "900.00",
    "currency": {
        "id": 1,
        "name": "USD",
        "symbol": "$",
        "exchange_rate": "1.00000000"
    }
}
```

Además de crear el producto, el controlador registra el precio base en `product_prices`, utilizando la misma divisa y el mismo importe.

**Nota:** el código actual carga únicamente la relación `currency` en esta respuesta. Para incluir también el registro de `product_prices` en el JSON, puedes cambiar `load('currency')` por `load(['currency', 'prices.currency'])`.

---

## 8. Consultar un producto

**Método:** `GET`

**Endpoint:**

```http
GET /api/products/1
```

No requiere cuerpo.

### Respuesta exitosa — `200 OK`

```json
{
    "id": 1,
    "name": "MacBook Pro M3",
    "description": "Portátil Apple",
    "price": "1499.99",
    "currency_id": 1,
    "tax_cost": "150.00",
    "manufacturing_cost": "900.00",
    "currency": {
        "id": 1,
        "name": "USD",
        "symbol": "$",
        "exchange_rate": "1.00000000"
    },
    "prices": [
        {
            "id": 1,
            "product_id": 1,
            "currency_id": 1,
            "price": "1499.99",
            "currency": {
                "id": 1,
                "name": "USD",
                "symbol": "$",
                "exchange_rate": "1.00000000"
            }
        }
    ]
}
```

El identificador `1` corresponde al ID del producto que se quiere consultar.

---

## 9. Actualizar un producto

**Método:** `PUT`

**Endpoint:**

```http
PUT /api/products/1
```

### Envío de datos

```json
{
    "name": "MacBook Pro M3 actualizado",
    "description": "Versión actualizada",
    "price": 1299.99,
    "currency": "EUR",
    "tax_cost": 130,
    "manufacturing_cost": 800
}
```

### Funcionamiento

1. Valida los datos recibidos.
2. Busca la divisa por su nombre.
3. Actualiza el producto y su divisa base.
4. Busca el precio correspondiente a esa divisa en `product_prices`.
5. Si existe, actualiza su importe; si no existe, lo crea.
6. Ejecuta las operaciones dentro de una transacción de base de datos.

### Respuesta exitosa — `200 OK`

```json
{
    "id": 1,
    "name": "MacBook Pro M3 actualizado",
    "description": "Versión actualizada",
    "price": "1299.99",
    "currency_id": 2,
    "tax_cost": "130.00",
    "manufacturing_cost": "800.00",
    "currency": {
        "id": 2,
        "name": "EUR",
        "symbol": "€",
        "exchange_rate": "1.08000000"
    }
}
```

Los valores de los tipos de cambio y los IDs son ilustrativos.

**Importante:** en el código actual, `currency` tiene la regla `required`. Por tanto, debes enviarla siempre, aunque el resto de los campos utilice `sometimes`. Si quieres permitir actualizaciones parciales sin cambiar la divisa, cambia esa regla por:

```php
'currency' => 'sometimes|required|string|exists:currencies,name',
```

---

## 10. Eliminar un producto

**Método:** `DELETE`

**Endpoint:**

```http
DELETE /api/products/1
```

No requiere cuerpo.

### Respuesta exitosa — `204 No Content`

La respuesta no contiene JSON porque el controlador devuelve un código HTTP `204`.

El producto se elimina. Si la clave foránea de `product_prices.product_id` está configurada con eliminación en cascada, también se eliminarán los precios relacionados.

---

## 11. Consultar los precios de un producto

**Método:** `GET`

**Endpoint:**

```http
GET /api/products/1/prices
```

No requiere cuerpo.

### Respuesta exitosa — `200 OK`

```json
[
    {
        "id": 1,
        "product_id": 1,
        "currency_id": 1,
        "price": "1499.99",
        "currency": {
            "id": 1,
            "name": "USD",
            "symbol": "$",
            "exchange_rate": "1.00000000"
        }
    },
    {
        "id": 2,
        "product_id": 1,
        "currency_id": 2,
        "price": "1399.99",
        "currency": {
            "id": 2,
            "name": "EUR",
            "symbol": "€",
            "exchange_rate": "1.08000000"
        }
    }
]
```

La respuesta contiene todos los precios registrados para el producto, junto con la información de sus divisas.

---

## 12. Agregar un precio en otra divisa

**Método:** `POST`

**Endpoint:**

```http
POST /api/products/1/prices
```

### Envío de datos

```json
{
    "currency": "EUR",
    "price": 1399.99
}
```

### Validaciones

| Campo | Regla |
|---|---|
| `currency` | Obligatorio, texto y debe existir en `currencies.name` |
| `price` | Obligatorio, numérico y mayor o igual que cero |

### Respuesta exitosa — `201 Created`

```json
{
    "id": 2,
    "product_id": 1,
    "currency_id": 2,
    "price": "1399.99",
    "currency": {
        "id": 2,
        "name": "EUR",
        "symbol": "€",
        "exchange_rate": "1.08000000"
    }
}
```

El cliente envía el nombre de la divisa, pero el servidor busca su ID para guardar la relación en `product_prices`.

### Intentar agregar una divisa duplicada

Si el producto ya tiene un precio en EUR, la API devuelve:

**Código HTTP:** `422 Unprocessable Entity`

```json
{
    "message": "Este producto ya tiene un precio en esa divisa."
}
```

El código impide duplicar el precio para la misma combinación de producto y divisa.

---

## 13. Errores de validación

Laravel devuelve automáticamente una respuesta JSON con código `422` cuando los datos no cumplen las reglas de validación.

### Ejemplo: precio negativo

Petición:

```json
{
    "name": "Producto de prueba",
    "description": "Ejemplo",
    "price": -100,
    "currency": "USD",
    "tax_cost": 10,
    "manufacturing_cost": 50
}
```

Respuesta:

```json
{
    "message": "The price field must be at least 0.",
    "errors": {
        "price": [
            "The price field must be at least 0."
        ]
    }
}
```

El texto exacto del mensaje puede variar según la versión y el idioma de validación de Laravel.

### Ejemplo: divisa inexistente

Petición:

```json
{
    "name": "Producto de prueba",
    "description": "Ejemplo",
    "price": 100,
    "currency": "XYZ",
    "tax_cost": 10,
    "manufacturing_cost": 50
}
```

Respuesta habitual:

```json
{
    "message": "The selected currency is invalid.",
    "errors": {
        "currency": [
            "The selected currency is invalid."
        ]
    }
}
```

### Producto inexistente

Si se solicita un ID que no existe, el enlace de modelo de Laravel normalmente devuelve:

**Código HTTP:** `404 Not Found`

El cuerpo de la respuesta depende de la configuración de excepciones de la aplicación.

---

## 14. Códigos HTTP utilizados

| Código | Significado |
|---|---|
| `200 OK` | Operación completada correctamente |
| `201 Created` | Producto o precio creado |
| `204 No Content` | Producto eliminado correctamente |
| `404 Not Found` | Producto o recurso inexistente |
| `422 Unprocessable Entity` | Datos inválidos o precio duplicado |
| `500 Internal Server Error` | Error inesperado en el servidor |

## 15. Reglas de negocio

- Cada producto tiene una divisa base y un precio base.
- Al crear un producto, su precio base se registra también en `product_prices`.
- Al actualizar un producto, el precio de la divisa base seleccionada se actualiza o se crea si no existe.
- Un producto puede tener precios en diferentes divisas.
- No se permite registrar dos precios para el mismo producto y la misma divisa mediante el endpoint de creación de precios.
- Los precios adicionales no se convierten automáticamente utilizando `exchange_rate`; se reciben explícitamente en la petición.

## 16. Consideraciones

- Los nombres de las divisas deben existir previamente en la tabla `currencies`.
- Los IDs de los ejemplos son ilustrativos y deben sustituirse por los registros reales de la base de datos.
- La API no incluye autenticación en las rutas mostradas.
- Para producción, conviene agregar autenticación, autorización, pruebas automatizadas y una política de manejo de errores.
- Se recomienda proteger la combinación `product_id` y `currency_id` con una restricción única en la base de datos para impedir duplicados incluso si llegan peticiones simultáneas.

## 17. Conclusión

La API permite administrar productos y mantener una lista de precios por divisa mediante endpoints REST. Los datos se intercambian en JSON, Laravel valida las entradas y Eloquent gestiona las relaciones entre productos, divisas y precios.
#   p r u e b a - b e g e n t o  
 