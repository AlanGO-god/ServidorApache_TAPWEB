# MiApp

Aplicación web con login y CRUD de usuarios, desarrollada con PHP, MySQL/MariaDB y Apache.

## Requisitos
- PHP 8+
- MariaDB/MySQL
- Apache con mod_php

## Instalación
1. Clonar el repositorio
2. Copiar `config/db.example.php` a `config/db.php` y configurar credenciales
3. Importar el esquema de base de datos (ver `schema.sql`)
4. Apuntar el DocumentRoot de Apache a la carpeta del proyecto

## Seguridad
- Contraseñas con `password_hash()` / `password_verify()`
- Consultas con PDO prepared statements
- Sesiones PHP para proteger el CRUD
