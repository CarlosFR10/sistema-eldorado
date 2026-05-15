# Guia de Cambio de Ruta / Migracion del Proyecto

## Resumen

El proyecto **NO depende de rutas fijas**. Todo funciona con rutas relativas y variables de entorno. Los unicos lugares con rutas absolutas son archivos de documentacion (README.md, SEGUIR.md) que muestran comandos de ejemplo del usuario.

---

## Arquitectura portable

```
CarpetaProyecto/
â”œâ”€â”€ docker-compose.yml    # Usa ./backend, ./frontend, ./nginx (relativos)
â”œâ”€â”€ backend/              # Codigo Laravel
â”œâ”€â”€ frontend/             # Codigo Vue
â”œâ”€â”€ nginx/                # Configuracion Nginx
â”œâ”€â”€ .env.example          # Variables de entorno (valores por defecto)
â””â”€â”€ .env                  # Variables reales (NO committed to git)
```

**Docker Compose usa rutas relativas:**
```yaml
volumes:
  - ./backend:/var/www/backend    # Cualquier ubicacion funciona
  - ./frontend:/var/www/frontend  # El proyecto puede moverse
  - ./nginx:/etc/nginx/conf.d     # Y seguir funcionando
```

---

## Que funciona automaticamente

### Rutas relativas (funcionan de cualquier ubicacion)

- `docker-compose.yml` - volumenes montados con `./ruta`
- `backend/artisan` - usa `__DIR__` para vendor
- `backend/public/index.php` - usa `__DIR__` para vendor
- `backend/config/*.php` - usa `storage_path()`, `resource_path()`
- `frontend/vite.config.js` - usa path resolved
- `frontend/src/api/axios.js` - usa `VITE_API_URL` o proxy

### Variables de entorno (funcionan de cualquier maquina)

```env
APP_KEY=base64:Qm5OMzRrVGRwRkxkTjRudTdYV2J4eXZMRGN6YTZYSjA=
DB_HOST=mysql                    # hostname Docker, no IP
DB_PASSWORD=eldorado2026
JWT_SECRET=eldorado-jwt-secret...
REVERB_APP_KEY=eldorado-key
```

### Nombres de contenedores (no cambian)

```yaml
sistema-eldorado-php-1
sistema-eldorado-nginx-1
sistema-eldorado-mysql-1
sistema-eldorado-redis-1
```

---

## Que archivos SI dependen de la ruta

Estos archivos tienen la ruta absoluta del usuario y deben actualizarse si se muda el proyecto:

### 1. README.md (lineas 58, 95)

```powershell
# ANTES (usuario especifico):
cd "nueva/ruta/al/proyecto"

# DESPUES (cualquier ubicacion):
cd "ruta/al/proyecto"
# o simplemente:
cd .
```

### 2. SEGUIR.md (lineas 347, 403, 415, 746)

Mismo caso - comandos con ruta completa del usuario.

---

## Como cambiar de ruta (migrar a otra PC o ubicacion)

### Opcion 1: Copiar la carpeta completa (recomendado)

1. Copiar toda la carpeta `sistema-eldorado` a la nueva ubicacion.
2. Asegurarse que Docker Desktop este corriendo.
3. Ejecutar:

```powershell
cd "nueva/ruta/a/sistema-eldorado"
docker compose up -d --build
docker compose exec php php artisan migrate:fresh --seed
docker compose exec php php artisan optimize:clear
```

### Opcion 2: Solo copiar archivos sin Docker volumes

1. Copiar la carpeta sin `node_modules`, `vendor`, ni volumenes Docker.
2. En la nueva ubicacion:

```powershell
docker compose up -d --build
docker compose exec php composer install
docker compose exec frontend npm install
docker compose exec php php artisan migrate:fresh --seed
```

---

## Archivos que contienen rutas absolutas (solo documentacion)

| Archivo | Contenido | Accion necesaria |
|---------|-----------|------------------|
| README.md | Lineas 58, 95 | Actualizar ejemplos de `cd` |
| SEGUIR.md | Lineas 347, 403, 415, 746 | Actualizar ejemplos de `cd` |

**NO afectan el funcionamiento del sistema.** Son solo ejemplos en la documentacion.

---

## Archivos que NO se deben modificar

- `docker-compose.yml` - usa rutas relativas `./backend`, etc.
- `backend/artisan` - usa `__DIR__`
- `backend/public/index.php` - usa `__DIR__`
- `backend/config/*.php` - usa `storage_path()`, `base_path()`
- `frontend/vite.config.js` - usa `path.resolve`
- Todos los controllers, models, services, views

---

## Verificar que el proyecto es portable

Ejecutar desde cualquier ubicacion:

```powershell
# Ver que docker-compose.yml existe y es valido
Get-Content docker-compose.yml | Select-String "backend" -Context 0,2

# Ver que las rutas relativas funcionan
Test-Path ./backend
Test-Path ./frontend
Test-Path ./nginx

# Levantar desde nueva ubicacion
docker compose up -d --build
```

---

## Si Docker no reconoce el proyecto

Si al copiar la carpeta Docker muestra error de que no encuentra el proyecto:

1. Verificar que Docker Desktop este corriendo:
```powershell
docker version
```

2. Verificar que no haya archivos de Docker en conflicto:
```powershell
docker compose ls
```

3. Ir a la carpeta correcta y ejecutar:
```powershell
cd "nueva/ruta/completa/al/proyecto"
docker compose up -d --build
```

---

## Cambiar nombre de la carpeta del proyecto

Si quieres renombrar `sistema-eldorado` a otro nombre:

1. Detener contenedores:
```powershell
docker compose down
```

2. Renombrar la carpeta.

3. Volver a levantar:
```powershell
cd "nuevo-nombre"
docker compose up -d --build
```

**Importante:** No cambiar el nombre de la carpeta mientras los contenedores estan corriendo.

---

## Variables de entorno necesarias

El archivo `.env` (creado desde `.env.example`) contiene todo lo necesario:

```env
APP_KEY=base64:Qm5OMzRrVGRwRkxkTjRudTdYV2J4eXZMRGN6YTZYSjA=
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=eldorado
DB_USERNAME=eldorado
DB_PASSWORD=eldorado2026

REDIS_HOST=redis
REDIS_PASSWORD=eldoradoRedis2026
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

JWT_SECRET=eldorado-jwt-secret-dev-2026-minimum-256-bit-key-para-desarrollo
QR_SECRET=supersecretoeldorado2026
BIOMETRIA_DRIVER=simulador

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=eldorado
REVERB_APP_KEY=eldorado-key
REVERB_APP_SECRET=eldorado-secret
REVERB_HOST=reverb
REVERB_PORT=8080
REVERB_SCHEME=http
```

Estas variables son las mismas en cualquier ubicacion. `DB_HOST=mysql` funciona porque Docker crea una red interna `eldorado_net` donde el hostname `mysql` resuelve al contenedor de MySQL.

---

## Volumenes Docker

Los volumenes nombrados se crean automaticamente y persisten los datos:

```yaml
volumes:
  mysql_data:        # Datos de MySQL
  redis_data:        # Datos de Redis
  backend_vendor:    # composer install resultados
  frontend_node_modules:  # npm install resultados
  frontend_dist:     # Build del frontend
```

Si cambias de PC y quieres partir de cero:
```powershell
docker compose down -v  # Elimina volumenes
docker compose up -d --build
docker compose exec php php artisan migrate:fresh --seed
```

---

## Checklist antes de cambiar de ubicacion

- [ ] Apagar contenedores: `docker compose down`
- [ ] Copiar toda la carpeta proyecto
- [ ] Verificar Docker Desktop corriendo
- [ ] En nueva ubicacion: `docker compose up -d --build`
- [ ] Esperar a que frontend termine de compilar (~1 minuto)
- [ ] Verificar: `docker compose ps`
- [ ] Probar: `http://localhost`

---

> Creado: 2026-05-14
> Motivo: Documentar portabilidad del proyecto para cambio de ruta/migracion