# Docker - Reorganización 2026-05-28

## Arquitectura

**5 contenedores:**

| Contenedor | Imagen | Función |
|------------|--------|---------|
| frontend | node:20-alpine | Construye Vue.js (npm install + build) |
| backend | php:8.2-fpm + supervisord | PHP-FPM + Queue + Scheduler + Reverb |
| nginx | nginx:1.25-alpine | Servidor web + proxy API |
| mysql | mysql:8.0 | Base de datos |
| redis | redis:7.2-alpine | Cache + Cola |

## Estructura de archivos

```
backend/
├── docker/
│   ├── nginx.conf        # Configuración nginx (proxy + serve frontend)
│   ├── supervisord.conf  # 4 procesos: php-fpm, queue, scheduler, reverb
│   └── entrypoint.sh     # Script de inicio
├── Dockerfile            # Build del backend con supervisord
└── .dockerignore        # Archivos excluidos del build

docker-compose.yml        # 5 servicios definidos
```

## Servicios en backend (supervisord)

1. **php-fpm** - Servidor PHP-FPM puerto 9000
2. **queue-worker** - `php artisan queue:work redis --sleep=3 --tries=3`
3. **scheduler** - `php artisan schedule:run` cada minuto
4. **reverb** - `php artisan reverb:start --host=0.0.0.0 --port=8080`

## API Endpoints

- `GET /api/public/rutas` - Lista de rutas (público)
- `POST /api/auth/login` - Login
- Y todos los demás endpoints de Laravel

## Volúmenes

- `mysql_data` - Datos de MySQL
- `redis_data` - Datos de Redis
- `backend_vendor` - Vendor de Composer (para no reinstallar)
- `frontend_node_modules` - Node modules del frontend
- `frontend_dist` - Build del frontend

## Cambios respecto a antes

- 8 contenedores → 5 contenedores
- Archivos Docker organizados en `backend/docker/`
- supervisor + supervisord en backend para 4 procesos
- nginx separado para servir frontend y proxy API

## Comandos

```bash
# Iniciar
docker-compose up -d

# Ver estado
docker ps

# Ver logs
docker-compose logs -f

# Reiniciar
docker-compose restart

# Parar
docker-compose down
```

## Commit

- `0ac7417` - Docker: 5 contenedores (frontend, backend+supervisord, nginx, mysql, redis)
