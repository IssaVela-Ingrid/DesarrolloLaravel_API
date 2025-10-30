 ## API RESTful: Sistema de Gestión de Usuarios



### 1. Introducción



Esta API (Application Programming Interface) fue desarrollada utilizando el framework Laravel para la gestión integral de usuarios. Implementa un sistema de autenticación seguro basado en JSON Web Tokens (JWT) y proporciona rutas protegidas para operaciones CRUD y la generación de estadísticas de registro.



## Proyecto: BOOTCAMP Full Stack Jr.

### Opción: API de Gestión de Usuarios (CRUD y Estadísticas)



### 2. Requisitos Previos



Antes de empezar, asegúrate de tener instalado:



PHP (v8.1+)



Composer



Laravel (v10 o superior)



Base de datos (MySQL o PostgreSQL recomendado)



### 3. Instalación y Configuración



3.1. Clonar el Repositorio



```

git clone [URL_DEL_REPOSITORIO]

cd [NOMBRE_DEL_PROYECTO]

```



3.2. Dependencias



Instalar las dependencias de Composer, incluyendo el paquete tymon/jwt-auth (o php-open-source-saver/jwt-auth):

```

composer install

```

3.3. Archivo de Entorno (.env)



Copia el archivo de ejemplo y configura tu conexión a la base de datos:

```

cp .env.example .env

php artisan key:generate

```

Asegúrate de que la conexión a la base de datos esté configurada:

```

DB_CONNECTION=mysql

DB_HOST=127.0.0.1

DB_PORT=3306

DB_DATABASE=nombre_de_tu_db

DB_USERNAME=usuario_db

DB_PASSWORD=clave_db

```

3.4. Configuración de JWT



Genera la clave secreta de JWT:

```

php artisan jwt:secret

```

Configuración de Expiración:

La configuración debe estar en config/jwt.php (o en la variable de entorno JWT_TTL). Se requiere una expiración de 5 minutos (300 segundos) para el token:



// En config/jwt.php

```

'ttl' => 300, // 5 minutos

```



3.5. Migraciones y Base de Datos



Ejecuta las migraciones para crear las tablas, incluyendo la tabla users con los campos necesarios (name, email, password, rol):

```

php artisan migrate --seed

```



Asegúrate de incluir un campo rol (por ejemplo: 'user', 'admin') para las rutas protegidas.



3.6. Ejecutar el Servidor

```

php artisan serve

```



4. Endpoints de la API con markdown
