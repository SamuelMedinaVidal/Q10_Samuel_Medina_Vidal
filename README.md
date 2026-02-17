# 🛒 Economik0

<div align="center">

![Symfony](https://img.shields.io/badge/Symfony-6.4-000000?style=for-the-badge&logo=symfony&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-≥8.1-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![Doctrine](https://img.shields.io/badge/Doctrine-ORM%203-FC6A31?style=for-the-badge&logo=doctrine&logoColor=white)

**Plataforma de comercio electrónico con diseño Glassmorphism**

[Características](#-características) •
[Instalación](#-instalación) •
[Uso](#-uso) •
[Arquitectura](#-arquitectura)

</div>

---

## 📋 Descripción

**Economik0** es una plataforma de comercio electrónico desarrollada con Symfony 6.4 que conecta proveedores con clientes. Permite a los proveedores gestionar su inventario de productos y a los clientes explorar un catálogo dinámico con sistema de carrito de compras.

### ✨ Características Principales

| Módulo | Descripción |
|--------|-------------|
| 🔐 **Autenticación** | Sistema completo con login, registro y roles |
| 👥 **Roles** | `ROLE_USER` (cliente) y `ROLE_PROVEEDOR` (vendedor) |
| 📦 **Gestión de Productos** | CRUD completo para proveedores |
| 🏪 **Tienda** | Catálogo con búsqueda y filtros por categoría |
| 🛒 **Carrito** | Sistema de carrito basado en sesión |
| 📝 **Solicitudes** | Formulario público para ser proveedor |
| 🎨 **UI/UX** | Diseño Glassmorphism con animaciones CSS |

---

## 🔧 Requisitos del Sistema

### Requisitos Obligatorios

| Componente | Versión Mínima | Recomendada |
|------------|----------------|-------------|
| PHP | 8.1 | 8.2+ |
| Composer | 2.0 | 2.6+ |
| MySQL/MariaDB | 5.7 / 10.4 | 8.0 / 10.11 |

### Extensiones PHP Requeridas

```
ext-ctype
ext-iconv
ext-pdo
ext-pdo_mysql
```

### Herramientas Opcionales

- **Symfony CLI** - Para servidor de desarrollo con soporte TLS
- **Git** - Para control de versiones

---

## 🚀 Instalación

### 1️⃣ Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/economik0.git
cd economik0
```

### 2️⃣ Instalar Dependencias

```bash
composer install
```

### 3️⃣ Configurar Variables de Entorno

Copia el archivo de ejemplo y configura tus credenciales:

```bash
cp .env .env.local
```

Edita `.env.local` con tu configuración:

```dotenv
# Configuración de Base de Datos
DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/economik0?serverVersion=8.0&charset=utf8mb4"

# Clave secreta de la aplicación (genera una nueva en producción)
APP_SECRET=tu_clave_secreta_aqui

# Entorno de ejecución
APP_ENV=dev
```

### 4️⃣ Variables de Entorno

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `DATABASE_URL` | Conexión a la base de datos | `mysql://root:@127.0.0.1:3306/economik0` |
| `APP_SECRET` | Clave para tokens CSRF y cookies | Cadena aleatoria de 32+ caracteres |
| `APP_ENV` | Entorno (`dev`, `prod`, `test`) | `dev` |

---

## 🗄️ Base de Datos

### Crear la Base de Datos

```bash
php bin/console doctrine:database:create
```

### Ejecutar Migraciones

```bash
php bin/console doctrine:migrations:migrate
```

### Estructura de Tablas

El proyecto incluye 3 migraciones que crean las siguientes tablas:

| Tabla | Descripción |
|-------|-------------|
| `user` | Usuarios con autenticación y roles |
| `product` | Productos con relación al vendedor |
| `contact` | Solicitudes para ser proveedor |

---

## ▶️ Ejecución

### Servidor de Desarrollo (Symfony CLI)

```bash
symfony server:start
```

### Servidor PHP Built-in

```bash
php -S localhost:8000 -t public/
```

### Acceso a la Aplicación

- **Página Principal:** http://localhost:8000
- **Tienda:** http://localhost:8000/tienda
- **Login:** http://localhost:8000/login
- **Registro:** http://localhost:8000/register

---

## 📖 Guía de Uso

### 🏠 Página Principal

La landing page presenta la marca Economik0 con un diseño minimalista de efecto cristal (Glassmorphism) y un orbe naranja animado como elemento visual distintivo.

---

### 👤 Sistema de Autenticación

#### Registro de Usuario

1. Accede a `/register`
2. Completa los campos: nombre, apellido, email y contraseña
3. Acepta los términos y condiciones
4. Por defecto obtienes el rol `ROLE_USER` (cliente)

#### Inicio de Sesión

1. Accede a `/login`
2. Introduce tu email y contraseña
3. Serás redirigido según tu rol:
   - **Clientes** → Tienda (`/tienda`)
   - **Proveedores** → Panel de productos (`/admin/productos`)

---

### 🏪 Tienda (Clientes)

#### Explorar Productos

- **URL:** `/tienda`
- **Acceso:** Requiere `ROLE_USER`

**Funcionalidades:**

| Función | Descripción |
|---------|-------------|
| 🔍 **Búsqueda** | Busca productos por nombre o categoría |
| 🏷️ **Filtros** | Filtra por categorías disponibles |
| 📱 **Responsive** | Grid adaptable a cualquier dispositivo |

#### Ver Detalle de Producto

- **URL:** `/tienda/producto/{id}`
- Muestra información completa: imagen, precio, stock, descripción
- Sección de productos relacionados (misma categoría)
- Botón para añadir al carrito

---

### 🛒 Carrito de Compras

- **URL:** `/cart`
- **Acceso:** Solo `ROLE_USER` (los proveedores no pueden comprar)

**Operaciones Disponibles:**

| Acción | Ruta | Descripción |
|--------|------|-------------|
| Ver carrito | `/cart` | Lista items con subtotales |
| Añadir | `/cart/add/{id}` | Agrega 1 unidad al carrito |
| Aumentar | `/cart/increase/{id}` | +1 unidad |
| Reducir | `/cart/decrease/{id}` | -1 unidad (elimina si llega a 0) |
| Eliminar | `/cart/remove/{id}` | Quita el producto completamente |
| Vaciar | `/cart/clear` | Elimina todos los productos |

> 💡 El carrito se almacena en la sesión del navegador.

---

### 📦 Panel de Proveedor

#### Gestión de Inventario

- **URL:** `/admin/productos`
- **Acceso:** Requiere `ROLE_PROVEEDOR`

**Operaciones CRUD:**

| Operación | Ruta | Descripción |
|-----------|------|-------------|
| 📋 Listar | `/admin/productos` | Ver todos tus productos |
| ➕ Crear | `/admin/productos/nuevo` | Añadir nuevo producto |
| ✏️ Editar | `/admin/productos/{id}/editar` | Modificar producto existente |
| 🗑️ Eliminar | `/admin/productos/{id}/eliminar` | Eliminar producto (POST) |
| 🔄 Toggle | `/admin/productos/{id}/toggle` | Activar/Desactivar producto |

#### Campos del Producto

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `nombre` | String(255) | ✅ | Nombre del producto |
| `precio` | Decimal(10,2) | ✅ | Precio en euros |
| `cantidad` | Integer | ✅ | Stock disponible |
| `categoria` | String(100) | ✅ | Una de las 5 categorías |
| `descripcion` | Text | ❌ | Descripción detallada |
| `imagen` | File | ❌ | Imagen del producto |
| `ubicacion` | String(255) | ❌ | Ubicación geográfica |
| `activo` | Boolean | ✅ | Visible en tienda |

#### Categorías Disponibles

```
• Frutas y Verduras
• Carnes y Embutidos
• Lácteos y Huevos
• Panadería y Bollería
• Conservas y Enlatados
```

---

### 📝 Solicitud de Proveedor

- **URL:** `/proveedor`
- **Acceso:** Público (sin autenticación)

Formulario para que usuarios externos soliciten convertirse en proveedores. Los datos se almacenan para revisión posterior por un administrador.

---

## 🏗️ Arquitectura del Proyecto

### Estructura de Directorios

```
economik0/
├── config/                 # Configuración de Symfony
│   ├── packages/           # Configuración de bundles
│   │   ├── doctrine.yaml   # ORM Doctrine
│   │   ├── security.yaml   # Autenticación y autorización
│   │   └── twig.yaml       # Motor de plantillas
│   └── routes/             # Definición de rutas
├── migrations/             # Migraciones de base de datos
├── public/                 # Directorio público (document root)
│   ├── css/styles.css      # Estilos CSS con variables
│   ├── uploads/            # Imágenes subidas
│   │   ├── productos/      # Imágenes de productos
│   │   └── proveedores/    # Imágenes de solicitudes
│   └── index.php           # Front controller
├── src/
│   ├── Controller/         # Controladores (lógica de rutas)
│   ├── Entity/             # Entidades Doctrine (modelos)
│   ├── Form/               # Tipos de formularios
│   ├── Repository/         # Repositorios (consultas a BD)
│   ├── Security/           # Handlers de autenticación
│   └── Service/            # Servicios de negocio
├── templates/              # Plantillas Twig
│   ├── admin/productos/    # Vistas del panel proveedor
│   ├── cart/               # Vistas del carrito
│   ├── page/               # Páginas estáticas
│   ├── partials/           # Componentes reutilizables
│   ├── registration/       # Registro de usuarios
│   ├── security/           # Login y dashboard
│   ├── tienda/             # Catálogo y detalle
│   └── base.html.twig      # Layout principal
└── var/                    # Cache y logs
```

### Controladores

| Controlador | Responsabilidad |
|-------------|-----------------|
| `PageController` | Páginas estáticas (home, about) |
| `SecurityController` | Login, logout, dashboard |
| `RegistrationController` | Registro de usuarios |
| `ContactController` | Formulario de solicitud proveedor |
| `ProductController` | CRUD de productos (proveedores) |
| `TiendaController` | Catálogo y detalle de productos |
| `CartController` | Operaciones del carrito |

### Entidades (Modelo de Datos)

```
┌─────────────┐       ┌─────────────┐
│    User     │       │   Contact   │
├─────────────┤       ├─────────────┤
│ id          │       │ id          │
│ email       │       │ firstName   │
│ password    │       │ name        │
│ roles[]     │       │ lastName    │
│ firstName   │       │ mail        │
│ lastName    │       │ message     │
└──────┬──────┘       │ img         │
       │              └─────────────┘
       │ vendedor
       │
┌──────▼──────┐
│   Product   │
├─────────────┤
│ id          │
│ nombre      │
│ precio      │
│ cantidad    │
│ descripcion │
│ imagen      │
│ categoria   │
│ activo      │
│ ubicacion   │
│ createdAt   │
│ updatedAt   │
└─────────────┘
```

### Servicios

| Servicio | Función |
|----------|---------|
| `CartService` | Gestión del carrito en sesión |
| `AuthenticationSuccessHandler` | Redirección post-login según rol |

### Jerarquía de Roles

```
ROLE_ADMIN
    └── ROLE_PROVEEDOR
            └── ROLE_USER
```

---

## 🎨 Frontend

### Stack de UI

- **Bootstrap 5.3** - Framework CSS (cargado vía CDN)
- **Inter Font** - Tipografía (Google Fonts, peso 400/700/800)
- **CSS Variables** - Sistema de diseño centralizado

### Sistema de Diseño

El CSS utiliza variables CSS para mantener consistencia:

```css
:root {
    /* Colores corporativos */
    --color-primary: #91ba41;        /* Verde Economik0 */
    --color-primary-dark: #7da336;   /* Verde hover */
    
    /* Glassmorphism */
    --glass-bg: rgba(255, 255, 255, 0.45);
    --glass-blur: blur(25px);
    
    /* Tipografía */
    --font-weight-extrabold: 800;
    --letter-spacing-tight: -0.06em;
}
```

### Características Visuales

- ✅ Efecto Glassmorphism (cristal translúcido)
- ✅ Orbe naranja animado con pulso
- ✅ Fuente Inter con peso 800
- ✅ Espaciado de letras negativo (-0.06em)
- ✅ Diseño responsive mobile-first

---

## ♿ Accesibilidad (WCAG 2.1 AA)

Economik0 está diseñado siguiendo las pautas **WCAG 2.1 Nivel AA** para garantizar una experiencia inclusiva para todos los usuarios, incluyendo personas con discapacidades visuales, motoras o cognitivas.

### ✅ Cumplimiento Implementado

| Criterio WCAG | Descripción | Implementación |
|---------------|-------------|----------------|
| **1.1.1** | Alternativas textuales | `alt` en todas las imágenes, `aria-label` en iconos |
| **1.3.1** | Info y relaciones | HTML semántico (`<main>`, `<nav>`, `<article>`, `<section>`) |
| **1.3.2** | Secuencia significativa | Orden lógico del DOM, heading hierarchy (h1→h2→h3) |
| **1.4.3** | Contraste mínimo | Ratio ≥4.5:1 en textos normales |
| **2.1.1** | Teclado | Todos los elementos interactivos son accesibles |
| **2.4.1** | Evitar bloques | "Skip to content" link para saltar navegación |
| **2.4.4** | Propósito del enlace | `aria-label` descriptivos en enlaces/botones |
| **2.4.7** | Focus visible | `:focus-visible` con outline destacado |
| **3.3.1** | Identificación de errores | `role="alert"` y `aria-live` en mensajes |
| **3.3.2** | Etiquetas e instrucciones | Labels vinculados, `aria-describedby` para ayudas |
| **4.1.2** | Nombre, función, valor | ARIA roles y atributos en componentes interactivos |

### 🔧 Características Técnicas

#### HTML Semántico
```html
<!-- Estructura de página accesible -->
<header>         <!-- Cabecera con navegación -->
<main>           <!-- Contenido principal con id="main-content" -->
<section>        <!-- Secciones con aria-labelledby -->
<article>        <!-- Contenido independiente -->
<nav>            <!-- Navegación con aria-label -->
<footer>         <!-- Pie de página -->
```

#### Skip Link (Saltar al contenido)
```html
<a href="#main-content" class="skip-link visually-hidden-focusable">
    Saltar al contenido principal
</a>
```

#### Formularios Accesibles
```html
<label for="email">Email <span aria-hidden="true">*</span></label>
<input id="email" 
       aria-required="true" 
       aria-describedby="email-help email-errors">
<small id="email-help">Introduce tu email</small>
<div id="email-errors" role="alert" aria-live="polite"></div>
```

#### Tablas con Encabezados
```html
<table aria-describedby="descripcion-tabla">
    <caption class="visually-hidden">Lista de productos</caption>
    <thead>
        <tr>
            <th scope="col">Producto</th>
            <th scope="col">Precio</th>
        </tr>
    </thead>
</table>
```

### 🎨 Estilos de Accesibilidad

El archivo `public/css/styles.css` incluye una sección dedicada a accesibilidad:

```css
/* Focus visible para navegación por teclado */
a:focus-visible,
button:focus-visible {
    outline: 3px solid var(--color-primary);
    outline-offset: 2px;
}

/* Respeto a preferencias del usuario */
@media (prefers-reduced-motion: reduce) {
    * { animation-duration: 0.01ms !important; }
}

/* Soporte para alto contraste */
@media (prefers-contrast: high) {
    .btn-primary { background: #000; }
}
```

### 🔍 Testing de Accesibilidad

Para verificar la accesibilidad del proyecto, recomendamos:

| Herramienta | Propósito |
|-------------|-----------|
| **WAVE** | Extensión del navegador para auditoría visual |
| **axe DevTools** | Análisis automatizado de accesibilidad |
| **Lighthouse** | Auditoría integrada en Chrome DevTools |
| **NVDA/VoiceOver** | Pruebas con lectores de pantalla reales |

### ⌨️ Navegación por Teclado

| Tecla | Acción |
|-------|--------|
| `Tab` | Navegar al siguiente elemento interactivo |
| `Shift+Tab` | Navegar al elemento anterior |
| `Enter` | Activar enlaces y botones |
| `Space` | Activar checkboxes y botones |
| `Esc` | Cerrar modales y menús |

---

## 🔒 Seguridad

### Configuración Implementada

- ✅ Hash de contraseñas con bcrypt (auto)
- ✅ Protección CSRF en formularios
- ✅ Control de acceso basado en roles
- ✅ Firewall configurado para rutas `/admin/*`
- ✅ Remember me con cookie segura (1 semana)
- ✅ Validación de propiedad de recursos

### Control de Acceso

| Ruta | Rol Requerido |
|------|---------------|
| `/admin/*` | `ROLE_PROVEEDOR` |
| `/tienda/*` | `ROLE_USER` |
| `/cart/*` | `ROLE_USER` |
| Resto | Público |

---

## 🧪 Comandos Útiles

### Desarrollo

```bash
# Limpiar caché
php bin/console cache:clear

# Verificar rutas
php bin/console debug:router

# Verificar servicios
php bin/console debug:container

# Validar esquema de BD
php bin/console doctrine:schema:validate
```

### Base de Datos

```bash
# Crear migración
php bin/console make:migration

# Ejecutar migraciones pendientes
php bin/console doctrine:migrations:migrate

# Revertir última migración
php bin/console doctrine:migrations:migrate prev
```

### Generadores

```bash
# Crear entidad
php bin/console make:entity

# Crear controlador
php bin/console make:controller

# Crear formulario
php bin/console make:form
```

---

## 📁 Assets y Uploads

### Estructura de Uploads

```
public/
└── uploads/
    ├── productos/      # Imágenes de productos (JPEG, PNG, WebP)
    └── proveedores/    # Documentos de solicitudes
```

### Compilación de Assets

Este proyecto **no utiliza Webpack Encore ni AssetMapper**. Los assets se sirven directamente:

- **CSS:** `public/css/styles.css`
- **JS:** `public/js/background.js`
- **Bootstrap/Inter:** CDN externos

Para cambios en CSS, simplemente edita el archivo y recarga el navegador.

---

## 🤝 Contribuir

1. Fork el repositorio
2. Crea una rama (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Añade nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

---

## 📄 Licencia

Este proyecto es de uso privado/educativo.

---

<div align="center">

**Desarrollado con ❤️ usando Symfony 6.4**

</div>
