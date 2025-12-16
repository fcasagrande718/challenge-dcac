# API REST de Productos con Frontend Básico

Este proyecto entrega una API REST sencilla para gestionar un catálogo de productos en PHP nativo y un frontend ligero en HTML, CSS y JavaScript para consumirla. Incluye contenedores Docker para la aplicación PHP y MySQL.

## Requisitos previos

- Docker y Docker Compose instalados.
- Puerto `8080` disponible para la aplicación web y `3306` para la base de datos (puertos configurables en `docker-compose.yml`).

## Configuración

1. **Variables de entorno principales**

   Las credenciales y el valor del dólar se definen en `docker-compose.yml`:

   ```yaml
   environment:
     DB_HOST: db
     DB_NAME: productos
     DB_USER: appuser
     DB_PASSWORD: applocal
     PRECIO_USD: 1000
   ```

   - `PRECIO_USD` debe contener el valor actual del dólar en pesos argentinos. La API calcula `precio_usd` dividiendo el precio en ARS por este valor.

2. **Construir y levantar los servicios**

   Desde la raíz del repositorio ejecuta:

   ```bash
   docker-compose up --build
   ```

   Esto construye la imagen PHP, habilita Apache con `mod_rewrite`, instala las extensiones de PDO para MySQL y crea la tabla `productos` mediante `docker/mysql-init.sql`.

3. **Acceder a la aplicación**

   - API: `http://localhost:8080/productos`
   - Frontend: `http://localhost:8080/frontend/`

## Endpoints disponibles

- `GET /productos` — Lista todos los productos (incluye `precio` en ARS y `precio_usd`).
- `GET /productos/{id}` — Obtiene un producto específico.
- `POST /productos` — Crea un producto. Cuerpo JSON:

  ```json
  {
    "nombre": "Laptop",
    "descripcion": "Equipo portátil",
    "precio": 150000.50
  }
  ```

- `PUT /productos/{id}` — Actualiza un producto. Cuerpo JSON igual al de creación.
- `DELETE /productos/{id}` — Elimina un producto.

## Frontend

La interfaz en `public/frontend/index.html` consume la API usando `fetch` y permite:

- Listar productos mostrando precios en ARS y USD calculados por la API.
- Crear nuevos productos.
- Editar productos existentes cargándolos en el formulario.
- Eliminar productos con confirmación.

Los mensajes de éxito o error se muestran en pantalla para facilitar el uso.

## Notas técnicas

- Se usa un patrón simple tipo ADR: el `Router` dirige las peticiones al `ProductController`, que utiliza `ProductRepository` para acceder a la base de datos y `Response` para formatear las respuestas JSON.
- La conexión a la base de datos está implementada como Singleton en `Database` usando PDO con manejo de excepciones.
- La conversión de moneda se realiza en la capa de lógica (`ProductController`) usando la variable de entorno `PRECIO_USD`.
- `public/.htaccess` activa la reescritura de rutas para que todas las solicitudes no estáticas pasen por `index.php`.

## Comandos útiles

- Detener y limpiar contenedores/volúmenes: `docker-compose down -v`
- Revisar los registros de la aplicación: `docker-compose logs app`
- Revisar los registros de MySQL: `docker-compose logs db`
