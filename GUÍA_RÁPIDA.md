# 🚀 Guía Rápida de Instalación - Sagras CI4

## Requisitos previos
- PHP 8.1 o superior
- Composer (https://getcomposer.org)
- MySQL / MariaDB (o XAMPP)
- Navegador web

## Paso 1: Crear proyecto CodeIgniter 4
```bash
composer create-project codeigniter4/appstarter sagras
cd sagras
```

## Paso 2: Copiar archivos del entregable
Descomprime el ZIP y copia las carpetas dentro del proyecto:
- Copia `app/Config/Routes.php` → `app/Config/Routes.php`
- Copia `app/Controllers/` → `app/Controllers/`
- Copia `app/Models/` → `app/Models/`
- Copia `app/Views/` → `app/Views/`
- Copia `.env.example` → raíz del proyecto

## Paso 3: Crear base de datos
1. Abre phpMyAdmin (http://localhost/phpmyadmin si usas XAMPP)
2. Ve a la pestaña "Importar"
3. Selecciona el archivo `sagras_db.sql`
4. Haz clic en "Importar"

## Paso 4: Configurar entorno
```bash
cp .env.example .env
```
Edita `.env` con tu editor de texto:
```ini
database.default.username = root
database.default.password = tu_contraseña
```

## Paso 5: Ejecutar
```bash
php spark serve
```
Abre tu navegador en: http://localhost:8080

## URLs para probar
- http://localhost:8080/pedidos → Consulta de pedidos
- http://localhost:8080/pedidos/nuevo → Registrar pedido
- http://localhost:8080/productos → Catálogo de productos

## Para convertir el documento a PDF
Abre `documento_entrega/Entregable_Final_Guia_Sagras.html` en Chrome y presiona Ctrl+P → Guardar como PDF.
