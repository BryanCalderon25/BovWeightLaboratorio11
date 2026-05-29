# BovWeight CR 🐄🤖

Plataforma de precisión para la estimación de peso bovino utilizando Inteligencia Artificial, basada en arquitectura limpia y diseño *agrotech premium* estilo Stitch.

## 🏗️ Arquitectura del Proyecto

El proyecto está dividido en 3 componentes principales que se comunican entre sí:

1. **`mobile-app/` (Frontend Móvil)**
   - Framework: Ionic 8 + Vue 3 (Composition API)
   - Gestión de estado: Pinia
   - Componentes creados manualmente con CSS puro (variables CSS, glassmorphism) para coincidir exactamente con el diseño solicitado.
   
2. **`backend/` (API RESTful)**
   - Framework: Laravel 12 (PHP 8.x)
   - Base de Datos: MySQL 8
   - Autenticación: Laravel Sanctum
   
3. **`ml-service/` (Microservicio de IA)**
   - Framework: Python Flask
   - Modelo: YOLOv8 (ultralytics) + OpenCV
   - Endpoint `/api/estimate` que recibe imágenes de Laravel y devuelve pesos simulados basados en detección de bounding boxes.

## 🚀 Guía de Instalación

### 1. Requisitos Previos
- Node.js 20+ y pnpm/npm
- PHP 8.3+ y Composer
- Python 3.11+
- Docker y Docker Compose (opcional pero recomendado)

### 2. Ejecutar con Docker (Recomendado para Backend y ML)

Desde la raíz del proyecto:
```bash
docker-compose up -d
```
Esto levantará la base de datos MySQL (puerto 3306) y el microservicio Python de IA (puerto 5000).

### 3. Configurar Frontend (Ionic App)

Debido a restricciones de tu terminal local con powershell, debes instalar las dependencias manualmente:

```bash
cd mobile-app
npm install  # o pnpm install
npm run dev
```
La aplicación móvil se ejecutará en `http://localhost:8100`. Puedes ver el diseño en tu navegador usando la vista de dispositivo móvil (F12).

### 4. Configurar Backend (Laravel)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## 🎨 Notas sobre el Diseño Frontend

Se ha implementado **estrictamente** la línea visual proporcionada en las capturas de Stitch:
- Paleta de colores verde oliva/tierra (`#414833`, `#656D4A`, `#7F4F24`).
- Tipografías *Work Sans* (para encabezados/métricas) e *Inter* (para cuerpo).
- Efectos modernos: *Glassmorphism*, botones interactivos, skeleton loaders, y tarjetas redondeadas con sombras suaves.
- Todas las variables y clases CSS han sido escritas en español para mantener consistencia.
- Datos de simulación (Demos) incluidos en las Stores de Pinia para que puedas ver y navegar por la interfaz de usuario inmediatamente sin necesitar el backend corriendo.

## 🔒 Variables de Entorno (.env)

El archivo `.env` del backend debe apuntar al microservicio ML:
```env
ML_SERVICE_URL="http://localhost:5000/api/estimate"
```

## 📱 Flujo de la Aplicación
1. **Splash Screen / Onboarding**: Animaciones fluidas presentando la app.
2. **Login**: Acceso seguro.
3. **Inicio (Dashboard)**: Métricas clave, accesos rápidos y actividad reciente.
4. **Animales**: CRUD de ganado con barra de búsqueda rápida.
5. **Pesaje**: Selección de foto (o cámara nativa), que envía la imagen a Laravel, que a su vez consulta al microservicio ML en Python.
6. **Historial**: Gráficos Chart.js interactivos.
7. **Fincas / Reportes**: Vistas de gestión territorial y exportación a PDF.
