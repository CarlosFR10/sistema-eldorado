# El Dorado - Sistema de Terminal de Buses

## Project Overview

Sistema web para gestion de pasajes, registro biometrico, control de abordaje con QR/huella y monitoreo GPS de buses de la Terminal El Dorado, Cochabamba - Bolivia.

Tesis: Carlos Alberto Cabezas Ramirez - UNITEPC 2026.

## Tech Stack

- **Backend**: Laravel 11, PHP 8.2, MySQL 8.0, Redis, Laravel Reverb (WebSockets)
- **Frontend**: Vue 3 (Composition API), Vite 6.4, TailwindCSS, PrimeVue, Pinia, Leaflet, PWA
- **Infra**: Docker, Nginx

## Important Rules

1. **Do not remake entire frontend** if asked to improve just one screen.
2. **Do not change `docker-compose.yml`** unless strictly necessary.
3. **Do not delete existing migrations**.
4. **Do not modify seeders** without checking that public purchase and seller still have available trips.
5. **Do not change router names** without updating buttons that navigate to those screens.
6. **In Vue `<script setup>`, always use `.value` to access refs** - auto-unwrapping only works in template.
7. **No quitar estados de viaje**: `en_venta`, `abordando`, `en_ruta`, `completado`, `cancelado`. ('programado' already removed)
8. **No quitar estados de boleto**: `pendiente_pago`, `pendiente_verificacion`, `pagado`, `abordado`, `cancelado`, `reembolsado`.

## Key Files

Start here for context:

- `SEGUIR.md` - Technical log, recent changes, pending work.
- `README.md` - Full project documentation.
- `backend/routes/api.php` - API endpoints map.
- `backend/app/Http/Controllers/GPS/GpsController.php` - GPS simulation with real waypoints, signal loss, auto-complete.
- `backend/app/Services/BoletoService.php` - Ticket issuance, minor linking, senior discount, QR and seat logic.
- `frontend/src/views/vendedor/MonitoreoMapaView.vue` - GPS monitoring with 2s polling, bus moving on map.
- `frontend/src/components/MapaGps.vue` - Leaflet map with start/destination markers and bus icon.
- `frontend/src/views/public/BoleteriaPublicaView.vue` - Public ticket purchase, real-time sync with active trips.
- `frontend/src/components/CroquisBus.vue` - Seat layout with colors: adult=indigo, senior=amber, adult+minor=pink, minor=red.

## GPS Simulation

The system has a GPS simulation feature:

- **8 routes with real waypoints**: CBB-LPZ, CBB-SCZ, CBB-ORU, CBB-PTS, CBB-SRE, CBB-TJA, CBB-TDD, CBB-CIJ
- **Simulation stored in-memory** in `$this->simulacionesActivas` array in GpsController
- **Duration**: 1 minute for testing (real trips would be hours)
- **Polling**: Frontend calls `POST /api/gps/viaje/{id}/avanzar` every 2 seconds
- **Signal loss**: 12.5% probability per call, 5-15 seconds duration
- **Auto-complete**: When progress >= 100%, trip is marked `completado` with `fecha_llegada_real`
- **ETA**: Calculated from remaining time based on elapsed/progress

## Before Closing Any Improvement

Always run:

```powershell
docker compose exec php php artisan test
docker compose up -d --build frontend nginx
docker compose exec php php artisan optimize:clear
```

Then test at least:
- `http://localhost`
- `http://localhost/boleteria`
- `http://localhost/consulta`
- `http://localhost/rastrear`
- `http://localhost/login`

## Docker Commands

```powershell
# Start everything
docker compose up -d --build

# Clear cache
docker compose exec php php artisan optimize:clear

# Reset database
docker compose exec php php artisan migrate:fresh --seed

# View logs
docker compose logs --tail 50 php
docker compose logs --tail 20 nginx

# Restart specific container
docker restart sistema-eldorado-frontend-1
```

## Demo Users

- Admin: `admin@eldorado.bo / Eldorado2026!`
- Supervisor: `supervisor@eldorado.bo / Eldorado2026!`
- Vendedor: `vendedor1@eldorado.bo / Eldorado2026!`
- Auxiliar: `auxiliar1@eldorado.bo / Eldorado2026!`

## Important URLs

- Public portal: http://localhost
- Buy tickets: http://localhost/boleteria
- Track bus: http://localhost/rastrear
- Authority query: http://localhost/consulta
- Login: http://localhost/login
- GPS Monitor: http://localhost/monitoreo-gps?viaje={id}