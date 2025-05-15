
# 📀 spotifyAPIChallenge

Aplicación API construida con Laravel que interactúa con la API de Spotify para ofrecer funcionalidades como la autenticación de usuarios en la aplicación y la obtención de datos relacionados con albums y artistas.

---

## 🚀 Descripción

Este proyecto es un desafío técnico que implementa una API rest en PHP utilizando el framework Laravel. 
Permite a los usuarios autenticarse con su cuenta de Spotify (mediante sus datos de API). La aplicación proporciona endpoints RESTful para interactuar con la API de Spotify y obtener información relevante para el usuario.

---

## 🛠️ Tecnologías utilizadas

- **Backend**: Laravel 12
- **Autenticación**: OAuth 2.0 con la API de Spotify / Implementada con Laravel Sanctum
- **Entorno de desarrollo**: Docker / Laravel Valet en macOS
- **Base de datos**: MySQL
---

## 📦 Instalación

1. Clona este repositorio en tu máquina local:

   ```bash
   git clone https://github.com/srraul94/spotifyAPIChallenge.git
   cd spotifyAPIChallenge
   ```

2. Copia el archivo `.env.example` a `.env` y configura las variables de entorno necesarias (BD, nombre de App...), incluyendo las credenciales de la API de Spotify.

   ```env
    SPOTIFY_API_TOKEN_URI=https://accounts.spotify.com/api/token
    SPOTIFY_CLIENT_ID='tu-client-id'
    SPOTIFY_CLIENT_SECRET='tu-client-secret'
    SPOTIFY_ACCESS_TOKEN='tu-access-token'
     ```

3. Construye y levanta los contenedores de Docker:

   ```bash
   docker-compose up -d
   ```

4. Accede al contenedor de la aplicación:

   ```bash
   docker-compose exec app bash
   ```

5. Instala las dependencias de PHP:

   ```bash
   composer install
   ```

6. Genera la clave de la aplicación:

   ```bash
   php artisan key:generate
   ```

7. Migra la base de datos:

   ```bash
   php artisan migrate
   ```

8. Inicia el servidor de desarrollo:

   ```bash
   php artisan serve
   ```

La aplicación estará disponible en [http://localhost:8000](http://localhost:8000).

---



## 🔐 Autenticación con Spotify

Para utilizar la API de Spotify, es necesario registrar tu aplicación en el [Spotify Developer Dashboard](https://developer.spotify.com/dashboard/applications) y obtener un `Client ID` y un `Client Secret`. Estas credenciales deben ser configuradas en el archivo `.env` para habilitar la autenticación OAuth 2.0.


---
## 📌 Notas adicionales

- Este proyecto es una implementación básica y puede requerir mejoras seguridad y optimización de rendimiento.
- Se recomienda revisar la documentación oficial de la API de Spotify para comprender mejor los endpoints disponibles y sus limitaciones.

---
