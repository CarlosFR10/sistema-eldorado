# SEGUIR.md - Bitacora Tecnica

> **REGLA IMPORTANTE:** Solo se mejora/toca lo que el usuario especificamente mencione.
> No re-hacer todo sin autorizacion. Documentar todo antes de implementar.

---

## SESSION ACTUAL - 2026-05-16

### Objetivo
Documentacion completa y perfecta. README.md exhaustivo y SEGUIR.md actualizado con todo lo hecho hasta el minimo detalle.

---

## SESION 2026-05-15 (completa) - GPS SVG unificados, ETA signal loss, mapa auto-show

### Archivos modificados (3 archivos):

**Frontend (3 archivos):**

1. `frontend/src/components/MapaGps.vue`
   - Marcadores SVG profesionales usando `L.divIcon`
   - Inicio: hexagono verde con cruz blanca
   - Fin: hexagono rojo con rectangulo blanco
   - Intermedios: circulo gris con numero
   - Bus: SVG estilizado con progreso (%) o "Sin seal"
   - Estilos CSS para quitar background residual

2. `frontend/src/views/public/RastrearBusView.vue`
   - Mapa visible automaticamente con `waypoints.length` (no solo "En ruta")
   - Polling activo para cualquier estado activo (excepto completado/cancelado)
   - Usa componente MapaGps en lugar de mapa inline
   - Badge de velocidad estimada durante signal loss ("~XX km/h")

3. `frontend/src/views/vendedor/ViajesActivosView.vue`
   - Modal de mapa con SVG unificados (mismo estilo que MapaGps)
   - Nueva funcion: `getModalWaypointIcon(index)` - hexagonos
   - Nuevo computed: `modalBusIcon` - bus SVG con progreso
   - Color de ruta: `#0f766e` (teal)
   - Badge "Senal GPS perdida" (no emoji)
   - Import `L from 'leaflet'`

**Backend (1 archivo):**

4. `backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php`
   - `etaMinutos()` recibe parametros: `(?Viaje $viaje, bool $signalLossActive = false, ?float $velocidadAnterior = null)`
   - Calculo de km no registrados durante signal loss
   - Casting correcto: `(float) $velocidadAnterior`, `(bool) $signalLossActive`
   - Cuando hay signal loss Y velocidad anterior, usa velocidad anterior para calcular ETA

---

### Flujo de prueba verificado:

1. Vendedor inicia viaje desde ViajesActivos -> viaje pasa a `en_ruta`
2. Rastrear muestra mapa automaticamente con waypoints
3. Bus se mueve por la ruta con progreso %
4. Durante signal loss: badge "Sin seal", velocidad estimada "~XX km/h"
5. Cuando progreso = 100%, viaje se completa automaticamente

---

## SESION 2026-05-15 (amanecer) - GPS duracion real y signal loss DB

### Archivos modificados:

**Backend:**
- `backend/app/Http/Controllers/GPS/GpsController.php`
  - DURACION_POR_DEFECTO_MINUTOS basada en distancia Haversine (70 km/h)
  - Metodo `calcularDistancia()` para distancia entre puntos
  - `iniciarViaje()`: devuelve `distancia_km` y `duracion_minutos`
  - `avanzarSimulacion()`: guarda `signal_loss` en DB

- `backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php`
  - `etaMinutos()`: calculo basado en ubicaciones GPS reales

- `backend/app/Models/UbicacionGps.php`
  - Agregado `'signal_loss'` a `$fillable`

- `backend/database/migrations/2026_05_14_000001_add_signal_loss_to_ubicaciones_gps_table.php`
  - Nueva migracion para columna `signal_loss`

**Frontend:**
- `frontend/src/views/public/RastrearBusView.vue`
  - Auto-refresco cada 5 segundos
  - Badge "Simulada"

---

## SESION 2026-05-14 (amanecer) - GPS simulacion basica

### Archivos modificados:

**Backend:**
- `backend/app/Http/Controllers/GPS/GpsController.php` (reescrito completo)
  - 8 rutas con waypoints reales
  - Logica: iniciar, avanzar, estado, calcularRumbo

**Frontend:**
- `frontend/src/views/vendedor/MonitoreoMapaView.vue` (reescrito completo)
  - Polling cada 2s con `pollAvanzar()`
  - Muestra estado, waypoints, progreso, ETA, seal

---

## SESION 2026-05-14 (noche) - Colores croquis y etiqueta "Mayor de edad"

### Archivos modificados:

- `frontend/src/components/CroquisBus.vue`
  - Colores mas oscuros y distintivos para asientos
  - Leyenda actualizada: "60+" -> "Mayor de edad"
  - Tooltip mejorado

---

## SESION 2026-05-12 (completa) - 10 horarios, sincronizacion, conflicto bus

### Archivos modificados (10 archivos):

- `backend/app/Http/Controllers/Viaje/ViajeController.php`
  - Regla conflicto bus: 2:30h minima
  - Horas disponibles con match exacto
  - Filtro estado en_venta

- `backend/routes/api.php`
  - GET /viajes/horas-disponibles

- `backend/database/seeders/ViajeSeeder.php`
  - 10 horas estandar: 06:00, 07:30, 09:00, 10:30, 12:00, 13:30, 15:00, 16:30, 18:00, 20:00

- `frontend/src/api/viajes.js`
  - Funcion horasDisponibles

- `frontend/src/views/public/BoleteriaPublicaView.vue`
  - cargarRutasConViajes() con Promise.all
  - Auto-refresco cada 8 segundos
  - Fix arrays en respuestas API

- `frontend/src/views/vendedor/CrearViajeView.vue`
  - Selector visual de horarios
  - Auto-seleccionar siguiente hora disponible

- `frontend/src/views/public/RastrearBusView.vue` (primeras versiones)
- `frontend/src/views/autoridad/ConsultaViajeView.vue`
- `frontend/src/components/BoletoDigital.vue`
- `frontend/src/views/vendedor/VentaBoletoView.vue`
- `frontend/src/views/vendedor/ViajesActivosView.vue`
- `frontend/src/stores/viaje.js`

---

## SESION 2026-05-11 - Registro publico, compra multiple, QR

### Archivos modificados:

- Registro publico `/registro`
- Compra publica con 1-10 pasajeros
- Menor enlazado con adulto responsable
- Consulta autoridad por codigo bus, placa, IMEI GPS, codigo viaje o QR
- Manifiesto con croquis y pasajeros
- PaymentSimulator con QR bancario y tarjeta
- Base de datos: 16 rutas, 20 buses, 8 viajes demo

---

## SESION 2026-05-10 - Portada publica, boleteria, PWA

### Archivos modificados:

- Portada publica animada
- Boleteria filtra rutas Cochabamba
- Boletos con boton rastrear
- Endpoints publicos para reservas y rastreo
- Croquis con colores para menores y adultos
- Vite 6.4.2 y PWA con limpieza de caches

---

## SESION 2026-05-09 - Base Laravel 11 completa

### Archivos modificados:

- Base Laravel 11 con auth JWT
- Modelos Eloquent con relaciones
- Servicios: bloqueo doble venta, menor/adulto, descuento mayor, QR firmado
- Seeders: 16 rutas, 20 buses, viajes demo
- Tests Pest/PHPUnit con SQLite en memoria
- Docker compose con Nginx, MySQL, Redis, Reverb

---

## Estado exacto donde quedamos (2026-05-16)

### Lo que esta funcionando:

1. **Registro publico** (`/registro`)
   - Sin huella verificada
   - Disponible para compra y venta

2. **Compra publica** (`/boleteria`)
   - Fecha, destino, horario
   - Croquis visual con seleccion de asientos
   - 1-10 pasajeros
   - Menor enlazado con adulto
   - Pago: efectivo, tarjeta, QR bancario

3. **Venta vendedor** (`/venta`)
   - Mismo flujo pero desde login
   - Validacion de pasajeros

4. **Rastreo GPS** (`/rastrear`)
   - Mapa con marcadores SVG profesionales automaticamente
   - Polling cada 5 segundos
   - Signal loss con velocidad estimada
   - Progreso %

5. **Consulta autoridad** (`/consulta`)
   - Por CI: lista de boletos
   - Por codigo viaje: manifiesto completo con croquis y pasajeros

6. **GPS Monitoreo** (`/monitoreo-gps?viaje={id}`)
   - Solo desde ViajesActivos
   - Polling cada 2 segundos
   - Bus se mueve por waypoints

7. **Viajes activos** (`/viajes-activos`)
   - Ver viajes en_venta, abordando, en_ruta
   - Crear viaje con selector de horarios
   - Iniciar viaje -> monitoreo

8. **Control abordaje** (`/abordaje`)
   - QR, huella o validacion dual

### ultimo commit:

```
d4cc9a8 Estado 2026-05-15: GPS SVG unificados, eta signal loss, mapa auto-show
```

### Estado del repositorio:

```
On branch master
nothing to commit, working tree clean
```

---

## Pendiente / siguiente sesion

1. Capturas de pantalla para defensa del tesis
2. Probar flujo completo: iniciar viaje -> rastrear muestra sincronizado
3. Ajustar textos finales de marca/logo
4. Crear repositorio GitHub para backup

---

## Comandos de verificacion (siempre ejecutar antes de cerrar sesion)

```powershell
# Ver estado de containers
docker compose ps

# Verificar que PHP tiene los cambios
docker exec sistema-eldorado-php-1 grep -n "etaMinutos\|signalLossActive" /var/www/backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php

# Test API rastreo
curl.exe -s "http://localhost/api/public/rastreo/VJ-20260515-001" | ConvertFrom-Json

# Correr tests
docker compose exec php php artisan test

# Ver logs PHP
docker compose logs --tail 20 php
```

---

## Reglas para futuras IAs

1. **No rehacer todo el frontend** si solo se pide mejorar una pantalla
2. **No cambiar docker-compose.yml** salvo que sea estrictamente necesario
3. **No borrar migraciones existentes**
4. **No modificar seeders** sin revisar que la compra publica y vendedor sigan teniendo viajes disponibles
5. **No cambiar nombres de rutas del router** sin actualizar los botones que navegan a esas pantallas
6. **No quitar estados de viaje:** en_venta, abordando, en_ruta, completado, cancelado
7. **No quitar estados de boleto:** pendiente_pago, pendiente_verificacion, pagado, abordado, cancelado, reembolsado

---

> Ultima actualizacion: 2026-05-16
> Responsable: Carlos Alberto Cabezas Ramirez - UNITEPC 2026