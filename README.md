# TechHouse — Arquitectura Backend de Microservicios (Laravel 11+)

Este repositorio contiene el backend completo de la plataforma **TechHouse**, implementado bajo una arquitectura de microservicios independientes (**Database-per-Service**) en Laravel 11+.

---

## 🏗️ Servicios y Puertos

| Servicio | Directorio | Puerto Local | Base de Datos (Prod/Dev) | Rol Principal |
|---|---|---|---|---|
| **Users Service** | [`users-service`](file:///c:/Users/Santiago/OneDrive/Escritorio/Repositorios/TechHouse/users-service) | `8001` | `techhouse_users` | Autenticación Sanctum, perfiles y roles |
| **Products Service** | [`products-service`](file:///c:/Users/Santiago/OneDrive/Escritorio/Repositorios/TechHouse/products-service) | `8002` | `techhouse_products` | Catálogo de productos, categorías y control de stock |
| **Payments Service** | [`payments-service`](file:///c:/Users/Santiago/OneDrive/Escritorio/Repositorios/TechHouse/payments-service) | `8003` | `techhouse_payments` | Gestión de órdenes y orquestación de checkout |
| **Assistant Service** | [`assistant-service`](file:///c:/Users/Santiago/OneDrive/Escritorio/Repositorios/TechHouse/assistant-service) | `8004` | `techhouse_assistant` | Chatbot asistente de compras y recomendaciones |

---

## 🔐 Seguridad y Comunicación entre Servicios

1. **Tokens de Servicio a Servicio (`service.auth`):**
   Las rutas internas no expuestas directamente a clientes públicos (como la consulta de usuarios desde pagos o la deducción atómica de inventario) están blindadas mediante el middleware `VerifyServiceToken`, que exige el encabezado:
   ```http
   X-Service-Token: techhouse_service_token_secret_2026
   ```
2. **CORS Unificado:**
   Cada microservicio tiene su [`config/cors.php`](file:///c:/Users/Santiago/OneDrive/Escritorio/Repositorios/TechHouse/users-service/config/cors.php) configurado para permitir `http://localhost:4200` (Angular) con `supports_credentials = true`.
3. **Manejo Centralizado de Excepciones:**
   En cada `bootstrap/app.php`, todas las rutas `api/*` responden siempre con el contrato estándar:
   ```json
   {
     "message": "Mensaje de error",
     "errors": { ... }
   }
   ```
4. **Flujo Atómico de Checkout con Compensación (Saga):**
   Al procesar un pago en `payments-service`:
   - Se crea la orden en estado `pending`.
   - Se descuenta el stock de cada producto mediante llamada HTTP segura con reintentos (`retry(2, 100)` y timeout de 5s) a `products-service`.
   - En `products-service`, `updateStock` bloquea el registro con `lockForUpdate()` para prevenir condiciones de carrera.
   - Si algún producto falla por falta de stock o caída de red, se ejecuta una compensación inmediata revirtiendo el stock ya descontado, la orden se marca como fallida y se devuelve HTTP 422.

---

## 🚀 Puesta en Marcha Rápida

### Opción 1: Con script automatizado PowerShell (Windows)
Desde la raíz de este proyecto:
```powershell
.\run-all.ps1
```
Este script abrirá 4 terminales independientes levantando cada servicio en su puerto correspondiente.

### Opción 2: Manual (terminal por terminal)
```bash
# Terminal 1: Users
cd users-service && php artisan serve --port=8001

# Terminal 2: Products
cd products-service && php artisan serve --port=8002

# Terminal 3: Payments
cd payments-service && php artisan serve --port=8003

# Terminal 4: Assistant
cd assistant-service && php artisan serve --port=8004
```

---

## 🧪 Ejecución de Pruebas Automatizadas

Cada microservicio cuenta con pruebas funcionales (Feature Tests):

```bash
# Users Service (Registro y Login)
cd users-service && php artisan test

# Products Service (Catálogo, Creación y Stock)
cd products-service && php artisan test

# Payments Service (Checkout exitoso y Rollback en fallo con mocks)
cd payments-service && php artisan test

# Assistant Service (Flujo de chat y persistencia)
cd assistant-service && php artisan test
```

---

## 📋 Resumen de Endpoints Principales

### Users Service (`:8001`)
- `POST /api/auth/register` — Registro de usuarios
- `POST /api/auth/login` — Login con rate limiting (`throttle:5,1`)
- `POST /api/auth/logout` — Logout (Sanctum)
- `GET /api/auth/me` — Perfil del usuario autenticado
- `GET /api/users/{id}` — Consulta interna (`X-Service-Token`)

### Products Service (`:8002`)
- `GET /api/products` — Listado con filtros (`?category=&min_price=&max_price=&search=`)
- `GET /api/products/{id}` — Detalle de producto
- `POST /api/products` — Creación de producto
- `PUT /api/products/{id}` — Edición de producto
- `PATCH /api/products/{id}/stock` — Ajuste transaccional de stock (`X-Service-Token`)
- `GET /api/categories` — Listado de categorías

### Payments Service (`:8003`)
- `POST /api/orders` — Creación de orden manual
- `GET /api/orders/{id}` — Detalle de orden con items y pago
- `GET /api/orders/user/{userId}` — Órdenes de un cliente
- `POST /api/payments/checkout` — Checkout orquestado con verificación de stock
- `GET /api/payments/{id}` — Detalle de transacción

### Assistant Service (`:8004`)
- `POST /api/assistant/chat` — Envío de mensaje al asistente con catálogo cacheado
- `GET /api/assistant/conversations/{userId}` — Historial de conversaciones
- `GET /api/assistant/conversations/detail/{id}` — Detalle y mensajes de una conversación
