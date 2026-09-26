<div align="center">
  <img src="https://capsule-render.vercel.app/api?type=waving&color=gradient&height=120&section=header&animation=fadeIn" />
</div>

<h1 align="center">🏠 TechHouse — Backend Microservicios</h1>

<h3 align="center">⚡ Arquitectura distribuida para comercio electrónico de tecnología con Laravel 11+, Sanctum e Inteligencia Artificial</h3>

<p align="center">
  Ecosistema backend modular bajo el patrón <b>Database-per-Service</b> para la gestión integral de usuarios, catálogo tecnológico, orquestación de pagos con saga/compensación transaccional y chatbot asistente de compras.<br>
  Construido con Laravel 11+, PHP 8.5+, MySQL / SQLite, Laravel Sanctum y comunicación segura inter-servicio.
</p>

---

### **PENDIENTE PARA EL PROYECTO**

- Conexión con el cliente Frontend en Angular (puerto 4200)
- Configuración de credenciales definitivas de MySQL en producción para las 4 bases de datos
- Integración de pasarela de pagos externa real (Stripe / Wompi / MercadoPago)
- Configuración de clave productiva para LLM externo en `assistant-service` (Gemini / OpenAI API)
- Despliegue en contenedores Docker y orquestación Cloud (Kubernetes / AWS ECS)

---

## 📋 **Descripción del Proyecto**

**TechHouse** es una plataforma de e-commerce especializada en hardware, componentes y tecnología de última generación. Su backend fue diseñado desde cero siguiendo una arquitectura desacoplada de microservicios, donde cada dominio de negocio opera con su propio ciclo de vida, persistencia de datos y reglas de autorización.

Los microservicios se comunican de forma síncrona mediante llamadas HTTP seguras con **Service Tokens**, tolerantes a fallos con reintentos y retroceso (*retry & backoff*), y orquestación transaccional con compensación (patrón Saga) para asegurar la consistencia del inventario ante cualquier fallo de red o stock.

> 🚀 **Estado del Proyecto:** Backend y Suite de Pruebas 100% Funcionales en Entorno Local

---

## 🏗️ **Arquitectura de Microservicios**

```
                            [ Cliente Angular :4200 ]
                                        │
           ┌────────────────────────────┼───────────────────────────┐
           ▼                            ▼                           ▼
┌─────────────────────┐      ┌─────────────────────┐      ┌─────────────────────┐
│    users-service    │      │  products-service   │      │  assistant-service  │
│     Puerto 8001     │      │     Puerto 8002     │      │     Puerto 8004     │
│  (Sanctum & Roles)  │      │ (Catálogo & Stock)  │      │  (Chatbot & Cache)  │
└─────────────────────┘      └──────────▲──────────┘      └──────────┬──────────┘
           ▲                            │                            │
           │                     X-Service-Token                     │
           │                            │ (Stock decrement)          │
           │                 ┌──────────┴──────────┐                 │
           └─────────────────┤  payments-service   │                 │
            (User reference) │     Puerto 8003     │                 │
                             │ (Órdenes & Checkout)│                 │
                             └─────────────────────┘                 │
                                        │ (Catálogo productos)       │
                                        └────────────────────────────┘
```

| Microservicio | Directorio | Puerto Local | Base de Datos | Rol Principal |
|---|---|---|---|---|
| **Users Service** | [`users-service`](file:///c:/Users/Santiago/OneDrive/Escritorio/Repositorios/TechHouse/users-service) | `8001` | `techhouse_users` | Autenticación Sanctum, perfiles, roles y rate limiting |
| **Products Service** | [`products-service`](file:///c:/Users/Santiago/OneDrive/Escritorio/Repositorios/TechHouse/products-service) | `8002` | `techhouse_products` | Catálogo de productos, categorías y stock transaccional |
| **Payments Service** | [`payments-service`](file:///c:/Users/Santiago/OneDrive/Escritorio/Repositorios/TechHouse/payments-service) | `8003` | `techhouse_payments` | Creación de órdenes y orquestación de checkout con saga |
| **Assistant Service** | [`assistant-service`](file:///c:/Users/Santiago/OneDrive/Escritorio/Repositorios/TechHouse/assistant-service) | `8004` | `techhouse_assistant` | Asistente de compras inteligente con caché y persistencia |

---

## 🔧 **Stack Tecnológico**

### **Frontend (SPA Angular 18 - Magnific AI Style)**
<div align="center">
  <img src="https://img.shields.io/badge/Angular_18-DD0031?style=for-the-badge&logo=angular&logoColor=white" />
  <img width="8" />
  <img src="https://img.shields.io/badge/TypeScript-007ACC?style=for-the-badge&logo=typescript&logoColor=white" />
  <img width="8" />
  <img src="https://img.shields.io/badge/Signals_State-DD0031?style=for-the-badge&logo=angular&logoColor=white" />
  <img width="8" />
  <img src="https://img.shields.io/badge/Glassmorphism_CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" />
</div>

### **Core Backend & Frameworks**
<div align="center">
  <img src="https://img.shields.io/badge/Laravel_11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img width="8" />
  <img src="https://img.shields.io/badge/PHP_8.5-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img width="8" />
  <img src="https://img.shields.io/badge/Composer-885630?style=for-the-badge&logo=composer&logoColor=white" />
  <img width="8" />
  <img src="https://img.shields.io/badge/Sanctum-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
</div>

### **Bases de Datos & Persistencia**
<div align="center">
  <img src="https://img.shields.io/badge/MySQL_8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img width="8" />
  <img src="https://img.shields.io/badge/SQLite_3-003B57?style=for-the-badge&logo=sqlite&logoColor=white" />
  <img width="8" />
  <img src="https://img.shields.io/badge/Docker_Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white" />
</div>

### **Testing & Calidad de Código**
<div align="center">
  <img src="https://img.shields.io/badge/PHPUnit_12-379C9C?style=for-the-badge&logo=php&logoColor=white" />
  <img width="8" />
  <img src="https://img.shields.io/badge/Feature_Tests-4caf50?style=for-the-badge&logo=checkmarx&logoColor=white" />
  <img width="8" />
  <img src="https://img.shields.io/badge/Guzzle_HTTP-00599C?style=for-the-badge" />
</div>

> 💾 **Estrategia de Datos:** *Database-per-Service*. Entorno de desarrollo local configurado con SQLite para ejecución inmediata sin dependencias externas, y plantilla `docker-compose.yml` lista para 4 instancias MySQL independientes.

---

## 🚀 **Estado Actual de Despliegue**

| Componente | Plataforma | Estado | URL / Puerto |
|---|---|---|---|
| **Users Service** | Servidor Local / Docker | ✅ 100% Funcional | `http://localhost:8001` |
| **Products Service** | Servidor Local / Docker | ✅ 100% Funcional | `http://localhost:8002` |
| **Payments Service** | Servidor Local / Docker | ✅ 100% Funcional | `http://localhost:8003` |
| **Assistant Service** | Servidor Local / Docker | ✅ 100% Funcional | `http://localhost:8004` |
| **Frontend (Angular)** | Angular CLI 18 | ✅ 100% Funcional | `http://localhost:4200` |

---

## 🏪 **Características del Sistema por Servicio**

### **👤 Users Service (`:8001`)**
- Autenticación segura mediante **Laravel Sanctum** (Personal Access Tokens).
- Control de roles (`Role` relation: admin, customer, vendor).
- Protección de endpoints críticos contra fuerza bruta con rate limiting (`throttle:5,1`).
- Transformación estricta con **UserResource** para no filtrar hashes de contraseñas.
- Endpoint interno `GET /api/users/{id}` protegido por token de servicio para consultas de órdenes.

### **📦 Products Service (`:8002`)**
- Catálogo completo con categorías jerárquicas y productos tecnológicos.
- Filtrado dinámico por categoría (`slug` o `id`), rango de precios (`min_price`, `max_price`) y búsqueda textual (`search`).
- Especificaciones técnicas en formato **JSON** nativo (`specs`).
- **Control atómico de inventario:** Endpoint `PATCH /api/products/{id}/stock` protegido con `service.auth`, validación de no-negatividad y bloqueo de fila con `lockForUpdate()` para prevenir condiciones de carrera concurrentes.

### **💳 Payments Service (`:8003`)**
- Gestión de órdenes (`Order`) con múltiples ítems (`OrderItem`) y estados (`pending`, `paid`, `failed`, `cancelled`).
- **Orquestación de Checkout con Patrón Saga:**
  1. Apertura de transacción de base de datos local.
  2. Solicitud atómica de descuento de stock a `products-service` con encabezado `X-Service-Token`, timeout de 5s y 2 reintentos.
  3. En caso de fallo o stock insuficiente: **compensación automática** revertiendo cualquier stock previamente reservado, marcado de orden como `failed`, rollback y respuesta HTTP 422 descriptiva.
  4. En caso exitoso: generación del comprobante `Payment` con código único (`TXN-XXXXXXXX`), transición a `paid` y respuesta HTTP 200.

### **🤖 Assistant Service (`:8004`)**
- Chatbot asistente de ventas y asesor tecnológico.
- **Persistencia garantizada:** Guarda cada turno del usuario antes de invocar servicios externos o modelos de lenguaje para nunca perder el historial.
- **Caché Inteligente:** Cachea el catálogo de `products-service` mediante `Cache::remember('products.catalog', 60, ...)` para reducir la latencia y evitar la saturación del servicio de productos.
- Motor de recomendación contextual basado en catálogo con fallback inteligente o conexión directa a LLMs (OpenAI / Gemini) vía `AI_API_KEY`.

---

## 🔐 **Seguridad y Contratos Globales**

- **Middleware `VerifyServiceToken` (`service.auth`):**
  Asegura que los endpoints de microservicio a microservicio no puedan ser consumidos por usuarios finales sin el header de autorización interno:
  ```http
  X-Service-Token: techhouse_service_token_secret_2026
  ```
- **CORS Homologado:**
  Los 4 microservicios permiten peticiones del frontend Angular (`http://localhost:4200`) admitiendo `supports_credentials = true`.
- **Estandarización de Errores JSON:**
  Configurado en `bootstrap/app.php` de cada servicio para que cualquier excepción en `api/*` responda con la misma estructura predecible:
  ```json
  {
    "message": "Mensaje informativo o error",
    "errors": { ... }
  }
  ```

---

## 🚀 **Cómo ejecutar el proyecto**

### Opción A: Inicio rápido en Windows (Un solo comando)
Desde la raíz del repositorio, ejecuta el script PowerShell incluido:
```powershell
.\run-all.ps1
```
*Este script levantará 4 terminales independientes con `php artisan serve` en sus respectivos puertos (8001, 8002, 8003, 8004).*

### Opción B: Inicio manual terminal por terminal
```bash
# Terminal 1: Users Service
cd users-service && php artisan serve --port=8001

# Terminal 2: Products Service
cd products-service && php artisan serve --port=8002

# Terminal 3: Payments Service
cd payments-service && php artisan serve --port=8003

# Terminal 4: Assistant Service
cd assistant-service && php artisan serve --port=8004
```

### Opción C: Levantar bases de datos MySQL con Docker Compose
Si deseas utilizar MySQL independiente por servicio en lugar de SQLite:
```bash
docker-compose up -d
```
*Creará 4 contenedores MySQL (`33061`, `33062`, `33063`, `33064`) listos para asociarse en cada `.env`.*

---

## 🧪 **Ejecución de Pruebas Automatizadas**

Cada microservicio incluye sus pruebas funcionales de extremo a extremo:

```bash
# Tests de Users Service (Registro y Login)
cd users-service && php artisan test

# Tests de Products Service (Catálogo y Stock atómico)
cd products-service && php artisan test

# Tests de Payments Service (Checkout exitoso y Rollback ante fallo)
cd payments-service && php artisan test

# Tests de Assistant Service (Persistencia de chat y recomendaciones)
cd assistant-service && php artisan test
```

---

## 👨‍💻 **Desarrollador**

<div align="center">
Santiago Arbelaez Contreras  
Junior Full Stack Developer  
Estudiante de Ingeniería de Sistemas – Universidad del Quindío

<br>
<a href="https://github.com/santiagoarbelaezc">
  <img src="https://img.shields.io/badge/GitHub-181717?style=for-the-badge&logo=github&logoColor=white" />
</a>
<img width="10" />
<a href="https://www.linkedin.com/in/santiago-arbelaez-contreras-9830b5290/">
  <img src="https://img.shields.io/badge/LinkedIn-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white" />
</a>
<img width="10" />
<a href="https://portfolio-santiagoa.web.app/portfolio">
  <img src="https://img.shields.io/badge/Portfolio-6C63FF?style=for-the-badge&logo=sparkles&logoColor=white" />
</a>
</div>

<div align="center">
  <img src="https://capsule-render.vercel.app/api?type=waving&color=gradient&height=90&section=footer&animation=fadeIn" />
</div>