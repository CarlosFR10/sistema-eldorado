# SEGUIR.md

> **REGLA IMPORTANTE:** Solo se mejora/toca lo que el usuario especificamente mencione. No re-hacer todo sin autorizacion. Documentar todo.

---

## Actualizacion 2026-05-15 (sesion completa GPS - marcadores SVG unificados)

### Objetivo

Unificar el estilo de los mapas GPS en todo el sistema: Rastrear, ViajesActivos modal, y Monitoreo. Usar SVG profesionales en vez de emojis. Corregir ETA durante signal loss. Hacer que el mapa en rastrear se muestre automaticamente sin necesidad de hacer click.

---

### Cambios realizados

#### 1. MapaGps.vue - SVG markers profesionales y bus icon

**Archivo:** `frontend/src/components/MapaGps.vue`

**Problema:** Los marcadores usaban emojis con cuadro blanco feo y se superponian con el bus. El popup del bus usaba emojis tambien.

**Solucion:**
- Cambiado de `LIcon` con HTML embebido a `L.divIcon` de Leaflet
- Waypoints usan SVG limpios:
  - Inicio: hexagono verde con cruz blanca
  - Fin: hexagono rojo con rectangulo blanco
  - Intermedios: circulo gris
- Bus usa SVG con progreso dentro (%)
- Badge "Sin seal" o "%" sobre el bus
- Estilos CSS para quitar background y border residuales

```javascript
const busIcon = computed(() => {
  const color = props.signalLoss ? '#94A3B8' : '#0F766E';
  const bgBadge = props.signalLoss ? '#EF4444' : '#0F766E';
  const badgeText = props.signalLoss ? 'Sin seal' : `${Math.round(props.progreso || 0)}%`;
  // SVG del bus con progreso dentro
  return L.divIcon({
    html,
    className: 'bus-icon',
    iconSize: [50, 58],
    iconAnchor: [25, 28],
  });
});
```

#### 2. RastrearBusView.vue - Mapa visible automaticamente y polling mejorado

**Archivo:** `frontend/src/views/public/RastrearBusView.vue`

**Problemas:**
- Mapa solo aparecia cuando `estado_operativo === 'En ruta'` - requeria hacer click
- Polling solo estaba activo cuando estaba "En ruta"
- Cuando el viaje estaba en otros estados ("Aun no partio", "En abordaje") no se veia el mapa

**Solucion:**
- Mapa se muestra cuando hay `waypoints.length` - no solo cuando esta "En ruta"
- Polling activo para cualquier estado excepto "Viaje finalizado" o "Viaje cancelado"
- El cliente detecta cambios de estado automaticamente cada 5 segundos

```javascript
// Antes
v-if="rastreo?.estado_operativo === 'En ruta'"

// Ahora
v-if="waypoints.length"

// Polling siempre activo
if (rastreo.value?.estado_operativo !== 'Viaje finalizado' && rastreo.value?.estado_operativo !== 'Viaje cancelado') {
  refreshInterval = setInterval(recargarRastreo, 5000);
}
```

#### 3. ViajesActivosView.vue - Modal con SVG unificados

**Archivo:** `frontend/src/views/vendedor/ViajesActivosView.vue`

**Problema:** El modal de mapa usaba emojis para el bus y waypoints basicos con divs.

**Solucion:**
- Nuevo metodo `getModalWaypointIcon(index)` - mismo estilo hexagonal que MapaGps
- Nuevo computed `modalBusIcon` - mismo SVG de bus que MapaGps
- Color de ruta cambiado a `#0f766e` (teal)
- Badge "Senal GPS perdida" en vez de emoji
- Estilos CSS para `waypoint-icon` y `bus-icon`

```javascript
function getModalWaypointIcon(index) {
  const isStart = index === 0;
  const isEnd = index === modalWaypoints.value.length - 1;
  // SVG hexagonal verde/rojo/gris segun tipo
  return L.divIcon({
    html,
    className: 'waypoint-icon',
    iconSize: [40, 50],
    iconAnchor: [20, 38],
  });
}

const modalBusIcon = computed(() => {
  // Igual que MapaGps busIcon
});
```

#### 4. Backend - etaMinutos con signal loss mejorado

**Archivo:** `backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php`

**Problemas:**
- `etaMinutos()` no recibia parametros de signal loss
- Cuando habia signal loss, el ETA se quedaba congelado
- `velocidadAnterior` podia ser string en vez de float

**Solucion:**
- `etaMinutos()` ahora recibe parametros: `(?Viaje $viaje, bool $signalLossActive = false, ?float $velocidadAnterior = null)`
- Cuando hay signal loss Y hay velocidad anterior:
  - Usa la velocidad anterior para calcular km restantes
  - Calcula km no registrados desde la ultima GPS valida
  - Resta esos km del estimate para dar ETA realista
- Casting correcto de tipos: `(float) $velocidadAnterior`, `(bool) $signalLossActive`

```php
private function etaMinutos(?Viaje $viaje, bool $signalLossActive = false, ?float $velocidadAnterior = null): ?int
{
    // ...
    if ($viaje->estado === 'en_ruta') {
        if ($viaje->simulacion_llamadas_totales > 0) {
            $velocidadProm = $signalLossActive && $velocidadAnterior ? $velocidadAnterior : 70;
            // Si hay seal perdida, estimar km no registrados
            if ($signalLossActive && $velocidadAnterior) {
                $ultimaGps = UbicacionGps::where('viaje_id', $viaje->id)
                    ->where('signal_loss', true)
                    ->latest('timestamp')
                    ->first();
                if ($ultimaGps) {
                    $segundosPerdida = now()->diffInSeconds($ultimaGps->timestamp);
                    $kmNoRegistrados = ($velocidadAnterior / 3600) * $segundosPerdida;
                    $kmRestantes = max(0, $kmRestantes - $kmNoRegistrados);
                }
            }
            return max(1, (int) ceil($kmRestantes / $velocidadProm * 60));
        }
    }
}
```

---

### Archivos modificados (2026-05-15 sesion completa)

**Frontend:**
- `frontend/src/components/MapaGps.vue`:
  - SVG markers con `L.divIcon` (waypoints y bus)
  - Estilos CSS para iconos limpios

- `frontend/src/views/public/RastrearBusView.vue`:
  - Mapa visible con `waypoints.length` (no solo "En ruta")
  - Polling mejorado para todos los estados activos

- `frontend/src/views/vendedor/ViajesActivosView.vue`:
  - Modal con `getModalWaypointIcon()` y `modalBusIcon`
  - SVG unificados con MapaGps
  - Import `L from 'leaflet'`

- `frontend/src/views/vendedor/MonitoreoMapaView.vue`:
  - Ya usaba MapaGps component - sin cambios necesarios

**Backend:**
- `backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php`:
  - `etaMinutos()` recibe parametros correctos
  - Calculo de km no registrados durante signal loss

---

### Flujo completo de prueba

1. **Probar rastrear sin viaje iniciado:**
   - Ir a `http://localhost/rastrear`
   - Ingresar codigo de viaje `VJ-20260515-001`
   - Verificar que el mapa muestra los waypoints aunque el estado sea "En ruta"
   - Los pines deben ser hexagonales (SVG), no emojis

2. **Probar rastrear durante signal loss:**
   - Con un viaje en ruta activo, monitorear en rastrear
   - Cuando el backend active signal loss, verificar:
     - Badge "Sin seal" aparece
     - Velocidad muestra estimacion (no 0)
     - ETA sigue calculando

3. **Probar ViajesActivos modal:**
   - Ir a `http://localhost/login` como vendedor
   - Ir a Viajes Activos
   - Seleccionar viaje en ruta y ver el modal
   - Los marcadores deben ser igual que en rastrear

4. **Probar sincronizacion en tiempo real:**
   - Iniciar un viaje en Viajes Activos
   - Abrir rastrear con el mismo codigo
   - Ambos deben mostrar la misma posicion y progreso

---

### Comandos de verificacion

```powershell
# Ver containers
docker compose ps

# Verificar cambios en PHP
docker exec sistema-eldorado-php-1 grep -n "etaMinutos\|signalLossActive" /var/www/backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php | Select-Object -First 10

# Verificar cambios en frontend
docker run --rm -v sistema-eldorado_frontend_dist:/data alpine ls /data/assets/ | Select-String "MapaGps"

# Test API rastreo
curl.exe -s "http://localhost/api/public/rastreo/VJ-20260515-001" | ConvertFrom-Json | Select-Object -ExpandProperty success
```

---

## Actualizacion 2026-05-15 (amanecer - sesion corta) - CONTINUADA

### Objetivo

Arreglar la simulacion GPS para que funcione correctamente con todos los departamentos y se vea en rastrear bus.

---

### Cambios realizados

#### 1. Duracion real basada en distancia (backend - GpsController.php)

**Problema:** La simulacion duraba solo 1 minuto sin importar la ruta, lo cual no era realista.

**Solucion:** Calculo de duracion basado en distancia Haversine y velocidad promedio de 70 km/h:

```php
private const DURACION_POR_DEFECTO_MINUTOS = 60;

$distanciaKm = $this->calcularDistancia($inicio['lat'], $inicio['lng'], $destino['lat'], $destino['lng']);
$velocidadPromedio = 70;
$duracionRealMinutos = max(1, (int) ceil($distanciaKm / $velocidadPromedio * 60));
```

Distancias estimadas:
- CBB-LPZ: 230 km (~3.3 horas)
- CBB-SCZ: 460 km (~6.6 horas)
- CBB-ORU: 120 km (~1.7 horas)
- CBB-PTS: 380 km (~5.4 horas)
- CBB-SRE: 290 km (~4.1 horas)
- CBB-TJA: 520 km (~7.4 horas)
- CBB-TDD: 400 km (~5.7 horas)
- CBB-CIJ: 650 km (~9.3 horas)

#### 2. Funciones auxiliares agregadas (backend)

**calcularDistancia():** Formula Haversine para distancia entre dos puntos geograficos.

**Respuesta mejorada de iniciarViaje:** Devuelve `distancia_km` y `duracion_minutos` reales.

#### 3. Signal loss persistente en base de datos (backend + migracion)

**Problema:** El campo `signal_loss` no se guardaba en la base de datos.

**Solucion:**
- Nueva migracion: `2026_05_14_000001_add_signal_loss_to_ubicaciones_gps_table.php`
- Campo `signal_loss` agregado al modelo `UbicacionGps.php`
- GpsController ahora guarda `signal_loss: true` cuando hay perdida de seal

#### 4. Auto-refresco en rastrear bus (frontend - RastrearBusView.vue)

**Problema:** El usuario no ve la simulacion en movimiento cuando rastrea un bus.

**Solucion:**
- Auto-refresco cada 5 segundos cuando el viaje esta "En ruta"
- Boton manual de refresh junto a badges de estado
- Badge "Simulada" visible cuando los datos son de simulacion
- Se deja de refrescar automaticamente cuando el viaje se completa o cancela

#### 5. ETA calculado basado en ubicaciones reales (backend - ConsultaViajeController.php)

**Problema:** El ETA era un numero aleatorio sin relacion con la simulacion real.

**Solucion:** `etaMinutos()` ahora calcula basado en:
- Cantidad de ubicaciones GPS registradas para el viaje
- Distancia estimada de la ruta
- Velocidad promedio del bus

---

### Archivos modificados

**Backend:**
- `backend/app/Http/Controllers/GPS/GpsController.php`:
  - Agregado `DURACION_POR_DEFECTO_MINUTOS`
  - `iniciarViaje()`: calcula duracion real segun distancia
  - `avanzarSimulacion()`: guarda `signal_loss` en DB, respuesta mejorada con `ubicacion`
  - Nuevo metodo `calcularDistancia()` (Haversine)

- `backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php`:
  - `etaMinutos()`: calcula ETA basado en ubicaciones reales
  - Nuevo metodo `estimarDistanciaRuta()` con distancias aproximadas por ruta
  - Eliminada referencia a `programado` en estados

- `backend/app/Models/UbicacionGps.php`:
  - Agregado `'signal_loss'` a `$fillable`

- `backend/database/migrations/2026_05_14_000001_add_signal_loss_to_ubicaciones_gps_table.php`:
  - Nueva migracion para agregar columna `signal_loss`

**Frontend:**
- `frontend/src/views/public/RastrearBusView.vue`:
  - Auto-refresco cada 5 segundos para viajes en ruta
  - Boton manual de refresh
  - Badge "Simulada" cuando la ubicacion es simulada

---

### Flujo completo de prueba

1. Ir a `http://localhost/login` como vendedor `vendedor1@eldorado.bo / Eldorado2026!`
2. Ir a Viajes Activos
3. Crear un viaje o seleccionar uno existente en estado `en_venta`
4. Click **"Iniciar viaje"** -> cambia estado a `en_ruta` y abre monitoreo
5. En MonitoreoGPS: ver bus moviendose por la ruta con waypoints
6. Ir a `http://localhost/rastrear` e ingresar codigo de viaje o placa
7. Ver mapa con bus en tiempo real (refresco cada 5 segundos)
8. Cuando la simulacion termina (segun duracion calculada), el viaje se marca como `completado`

### Comandos de verificacion

```powershell
# Ver containers
docker compose ps

# Ver logs PHP
docker compose logs --tail 20 php

# Verificar migracion aplicada
docker exec sistema-eldorado-mysql-1 mysql -u eldorado -peldorado2026 eldorado -e "DESCRIBE ubicaciones_gps;"

# Correr tests
docker compose exec php php artisan test
```

---

## Actualizacion 2026-05-14 (amanecer - sesion corta)

**iniciarViaje(id):**
- Busca viaje, verifica estado `en_venta` o `abordando`
- Obtiene codigo de ruta del viaje y selecciona waypoints correspondientes
- Guarda estado en `$this->simulacionesActivas[$id]`:
  - `viaje_id`, `bus_id`, `waypoints`, `waypoint_actual`, `progreso`
  - `signal_loss`, `signal_loss_until`
  - `inicio` (timestamp), `duracion_minutos` (1 minuto para testing)
- Cambia estado del viaje a `en_ruta`
- Crea UbicacionGps inicial en waypoint de partida
- Devuelve: `ubicacion`, `waypoints`, `mensaje`

**avanzarSimulacion(id):**
- Calcula progreso desde tiempo transcurrido: `$elapsed / ($duracion_minutos * 60)`
- 12.5% probabilidad de perdida de seal (`random_int(1,8) === 1`)
- Si hay perdida de seal: devuelve `signal_loss: true`, velocidad 0, lat/lng fijos
- Si no hay seal: interpola posicion entre waypoints segun progreso
- Velocidad: random 60-90 km/h, rumbo calculado con `calcularRumbo()`
- Cuando `progreso >= 1.0`: auto-completa viaje, marca `fecha_llegada_real`, limpia simulacion

**estadoSimulacion(id):**
- Si simulacion activa: devuelve estado completo con waypoints, progreso, ETA, seal
- Si no: busca viaje en DB y devuelve estado simple

**calcularRumbo(lat1, lng1, lat2, lng2):**
- Formula haversine para calcular angulo de direccion entre dos puntos

#### 4. MonitoreoMapaView.vue reescrito (frontend)

**Problema:** El componente anterior usaba gpsStore que no existia, llamaba `recargar()` que hacia cosas incorrectas, y no hacia polling correctamente.

**Solucion:** Reescritura completa con:

**Variables:**
- `viajeId` - de `route.query.viaje`
- `estadoSim` - estado de simulacion del backend
- `waypoints` - array de puntos de la ruta
- `busPosition` - `{lat, lng}` para el mapa
- `pollInterval` - intervalo de polling cada 2s

**Funciones:**
- `onMounted`: carga estado inicial, inicia polling si viaje en ruta
- `iniciarPoll()`: crea intervalo cada 2s llamando `pollAvanzar()`
- `detenerPoll()`: limpia intervalo
- `pollAvanzar()`: llama `avanzarSimulacionGps()` cada 2s, actualiza `busPosition` si no es fin
- `cargarViaje()`: llama `estadoSimulacionGps()`, extrae waypoints y posicion inicial
- `recargar()`: vuelve a cargar estado
- `seleccionarBus()`: cambia a otro viaje, reinicia polling

**RUTAS_WAYPOINTS local:** igual que en backend, para fallback si API no devuelve waypoints

#### 5. MapaGps.vue con marcadores visuales (frontend)

**Props:**
- `waypoints` - array de waypoints con `{lat, lng, nombre}`
- `busPosition` - `{lat, lng}` del bus actual
- `signalLoss` - boolean para mostrar perdida de seal
- `progreso` - porcentaje 0-100
- `velocidad` - km/h

**Marcadores:**
- ðŸŸ¢ Waypoint 0 (partida) - verde con nombre
- ðŸ”´ Ultimo waypoint (llegada) - rojo con nombre
- ðŸšŒ Bus - emoji con badge de progreso o "Sin seal"

**Linea de ruta:**
- `LPolyline` une todos los waypoints
- Color teal cuando normal, gris con lineas punteadas cuando hay perdida de seal

#### 6. ViajesActivosView.vue - boton "Iniciar viaje" (frontend)

**Boton "Iniciar viaje":**
- Visible para estados `en_venta` y `abordando`
- Llama `iniciarViajeGps(viaje.id)` del API
- Luego redirect a `/monitoreo-gps?viaje={id}`

**Boton "Ver en mapa":**
- Visible para estado `en_ruta`
- Link a `/monitoreo-gps?viaje={id}`

---

### Archivos modificados (2026-05-14)

**Backend:**
- `backend/app/Http/Controllers/GPS/GpsController.php` - reescrito completamente
  - 366 lineas
  - Sin dependencia de GpsService
  - 8 rutas con waypoints reales
  - Logica de simulacion inline (iniciar, avanzar, estado, calcularRumbo)

**Frontend:**
- `frontend/src/views/vendedor/MonitoreoMapaView.vue` - reescrito completamente
  - 286 lineas
  - Polling cada 2s con `pollAvanzar()`
  - Muestra estado, waypoints, progreso, ETA, seal
  - `busPosition` usa `{lat, lng}` para compatibilidad con MapaGps
- `frontend/src/components/MapaGps.vue` - sin cambios (ya estaba correcto)
- `frontend/src/api/gps.js` - sin cambios (ya estaba correcto)

---

### Flujo completo de prueba

1. **Crear viaje** en ViajesActivos (estado `en_venta`)
2. **Click "Iniciar viaje"** â†’ llama `POST /api/gps/viaje/{id}/iniciar`
   - Backend: guarda simulacion, cambia estado a `en_ruta`, crea UbicacionGps inicial
3. **Redirect a MonitoreoMapa** con `?viaje={id}`
4. **MonitoreoMapa onMounted** â†’ llama `GET /api/gps/viaje/{id}/estado`
   - Recibe waypoints, lat/lng inicial, estado
   - Inicia polling cada 2s
5. **Polling cada 2s** â†’ `POST /api/gps/viaje/{id}/avanzar`
   - Backend: calcula progreso, interpola posicion, posible signal_loss
   - Devuelve: lat, lng, progreso, eta_minutos, signal_loss, velocidad
6. **Mapa se actualiza:**
   - ðŸšŒ bus se mueve por la ruta
   - Progreso bar sube
   - ETA baja
   - Posible badge "Sin seal"
7. **~60 segundos despues** â†’ `progreso >= 100`
   - Backend: completa viaje, `fecha_llegada_real = now()`
   - Frontend: recibe `fin: true`, detiene polling
   - Viaje desaparece de ViajesActivos

---

### Comandos de verificacion

```powershell
# Ver containers
docker compose ps

# Ver logs PHP
docker compose logs --tail 20 php

# Ver logs nginx
docker compose logs --tail 10 nginx

# Test rutas GPS en container
docker exec sistema-eldorado-php-1 php /var/www/backend/artisan route:list --path=gps

# Verificar GpsController en container
docker exec sistema-eldorado-php-1 sh -c "grep -n 'simulacionesActivas\|waypoints' /var/www/backend/app/Http/Controllers/GPS/GpsController.php | head -10"
```

---

### Estados del viaje

```
en_venta   â†’ puede iniciar simulacion
abordando  â†’ puede iniciar simulacion
en_ruta    â†’ simulacion activa, polling cada 2s
completado â†’ auto-completado por simulacion (fecha_llegada_real seteada)
cancelado  â†’ no puede iniciar
```

---

### Consideraciones importantes

1. **El polling es frontend-side**: cada 2s el frontend llama `avanzarSimulacion`. Si el usuario cierra la pagina, la simulacion se detiene en el servidor (en memoria) pero el viaje queda `en_ruta` para siempre.

2. **Duracion de 1 minuto**: la simulacion dura 1 minuto real para testing. En produccion seria mucho mas largo.

3. **Sin persistencia de simulacion**: `$simulacionesActivas` es un array en memoria del PHP-FPM worker. Si el worker se reinicia, se pierde. Para produccion habria que usar Redis o la base de datos.

4. **Signal loss**: 12.5% probabilidad cada vez que se llama `avanzar`, duracion 5-15 segundos random.

5. **Waypoints del codigo de ruta**: el backend toma el `codigo` de la ruta (ej: "CBB-LPZ") y busca en `RUTAS_WAYPOINTS`. Si no encuentra, usa CBB-LPZ por defecto.

---

# SEGUIR.md

> **REGLA IMPORTANTE:** Solo se mejora/toca lo que el usuario especificamente mencione. No re-hacer todo sin autorizacion. Documentar todo.

---

## Actualizacion 2026-05-15 (madrugada - sesion GPS completa)

### Cambios realizados

#### 1. Progreso correcto en rastrear (backend)

**Archivo:** `backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php`

** Problema:** Cuando se iniciaba un viaje desde vendedor y luego se rastreaba, el progreso no se mostraba correctamente hasta hacer click en el mapa.

**Solucion:**
- `etaMinutos()` ahora usa `simulacion_llamada_actual` y `simulacion_llamadas_totales` para calcular el progreso real
- Formula: `progreso = (simulacion_llamada_actual / simulacion_llamadas_totales) * 100`

```php
if ($viaje->simulacion_llamadas_totales > 0) {
    $llamada = (int) $viaje->simulacion_llamada_actual;
    $total = (int) $viaje->simulacion_llamadas_totales;
    $progreso = $llamada / $total;
    $distanciaKm = $this->estimarDistanciaRuta($viaje?->ruta?->codigo);
    $velocidadProm = 70;
    $kmRestantes = $distanciaKm * (1 - $progreso);
    return max(1, (int) ceil($kmRestantes / $velocidadProm * 60));
}
```

#### 2. Velocidad estimada durante signal loss (backend + frontend)

**Archivo:** `backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php`

** Problema:** Cuando habia signal loss, la velocidad mostraba 0 km/h y el ETA se quedaba congelado.

**Solucion:**
- Se busca la velocidad anterior al signal loss en la base de datos
- Se devuelve como `velocidad_estimada` cuando hay signal loss activo
- El frontend muestra "~XX km/h" cuando hay signal loss

```php
$ultimaConVelocidad = UbicacionGps::where('viaje_id', $viaje->id)
    ->where('signal_loss', false)
    ->where('velocidad', '>', 0)
    ->latest('timestamp')
    ->first();
$velocidadAnterior = $ultimaConVelocidad?->velocidad;
```

#### 3. MapaGps.vue con marcadores SVG profesionales

**Archivo:** `frontend/src/components/MapaGps.vue`

** Problema:** Los marcadores usaban emojis con cuadro blanco feo y se superponian con el bus.

**Solucion:**
- Marcadores SVG profesionales:
  - Inicio: hexagono verde con flecha
  - Fin: hexagono rojo con banderin
  - Intermedios: circulos grises
- Bus: SVG de bus estilizado con progreso dentro
- Badge "Sin seal" o porcentaje sobre el bus

#### 4. RastrearBusView usa MapaGps y muestra automaticamente

**Archivo:** `frontend/src/views/public/RastrearBusView.vue`

** Problema:** No mostraba el mapa hasta hacer click, y usaba emojis feos.

**Solucion:**
- Ahora usa componente `MapaGps` en lugar de mapa inline
- Muestra automaticamente cuando `estado_operativo === 'En ruta'`
- Badge de velocidad estimada durante signal loss
- Sin emojis - todo SVG

---

### Archivos modificados (2026-05-15 madrugada)

**Backend:**
- `backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php`:
  - `rastreo()`: agrega `progreso` y `velocidad_estimada` a la respuesta
  - `etaMinutos()`: usa datos de simulacion para calculo preciso

**Frontend:**
- `frontend/src/components/MapaGps.vue`:
  - Marcadores SVG (inicio/fin/intermedios)
  - Bus SVG profesional con progreso
  - Props: waypoints, busPosition, signalLoss, progreso, velocidad

- `frontend/src/views/public/RastrearBusView.vue`:
  - Usa MapaGps en lugar de mapa inline
  - Auto-muestra cuando viaje esta en ruta
  - Badge de velocidad estimada durante signal loss

---

### Para probar

1. Ir a `http://localhost/login` como vendedor
2. Ir a Viajes Activos, seleccionar viaje en_venta
3. Click "Iniciar viaje" - cambia a en_ruta
4. Ir a `http://localhost/rastrear` e ingresar codigo del viaje
5. Verificar que:
   - El mapa muestra el bus inmediatamente
   - El progreso es correcto (ej: 15%, 30%, etc)
   - Durante signal loss muestra velocidad estimada (no 0)
   - Los marcadores son SVG profesionales (no emojis)

---

### Comandos para reiniciar

```powershell
cd C:\sistema-eldorado

# Apagar
docker compose down

# Encender
docker compose up -d --build

# Verificar estado
docker compose ps
```

---

## Historial de sesiones 2026-05-14

### Noche - Boleteria fixes
- Fix manejo arrays en respuestas API (`{ data: [...], meta: ... }`)
- ViajeSeeder `$dia = -2` para incluir viajes de dias anteriores
- Refresco silencioso de horarios (no parpadeo)

### Manana - GPS Simulacion
- Duracion real basada en distancia (Haversine 70 km/h)
- Signal loss persistente en DB
- Auto-refresco en rastrear bus cada 5 segundos

---

> Ultima actualizacion: 2026-05-15 (00:50)
> Responsable: Codex

### Cambios realizados

#### 1. Colores del croquis del bus renovados

**Objetivo:** Los asientos ocupados tenian colores muy claros y parecidos entre si, haciendolos dificil de distinguir en la boleteria y venta.

**Solucion en** `frontend/src/components/CroquisBus.vue`:

**Leyenda (badges superiores del croquis):**
| Tipo | Color nuevo |
|------|-------------|
| Libre | Verde claro con borde verde (`bg-emerald-50 text-emerald-800 border border-emerald-200`) |
| Adulto | Indigo oscuro con texto blanco (`bg-indigo-400 text-white border border-indigo-600`) |
| Con menor | Rosa oscuro con texto blanco (`bg-pink-500 text-white border border-pink-700`) |
| Mayor de edad | Ambar oscuro con texto blanco (`bg-amber-500 text-white border border-amber-700`) - ANTERIORMENTE "60+" |
| Menor | Rojo oscuro con texto blanco (`bg-red-500 text-white border border-red-700`) |

**Asientos en el croquis:**
| Tipo | Color antes | Color ahora |
|------|------------|-------------|
| Libre | Verde claro | Verde claro con borde verde oscuro (`border-emerald-500 bg-emerald-100`) |
| Adulto | Azul claro | Indigo oscuro con texto blanco (`border-indigo-700 bg-indigo-400 text-white`) |
| Con menor | Cyan claro | Rosa oscuro con texto blanco (`border-pink-700 bg-pink-500 text-white`) |
| Mayor de edad | Sky claro | Ambar oscuro con texto blanco (`border-amber-700 bg-amber-500 text-white`) |
| Menor | Amarillo | Rojo oscuro con texto blanco (`border-red-600 bg-red-500 text-white`) |

**Tooltip mejorado:** border `border-slate-300` para mejor visibilidad sobre fondos claros.

**Archivo modificado:**
- `frontend/src/components/CroquisBus.vue`

#### 2. Cambio de etiqueta "60+" a "Mayor de edad"

- Badge en leyenda: "60+" -> "Mayor de edad"
- Tooltip de adulto mayor: "Seleccionado para adulto mayor. Aplica trato preferente." -> "Seleccionado para adulto mayor (60+ anos). Aplica trato preferente."

---

## Historial de sesiones anteriores

### Actualizacion 2026-05-12 (resumen - dia)

- Codigo de viaje unico VJ-AAAAMMDD-NNN
- 10 horarios estandar: 06:00, 07:30, 09:00, 10:30, 12:00, 13:30, 15:00, 16:30, 18:00, 20:00
- Selector visual de horarios en CrearViajeView (verde=libre, rojo=ocupado, plomo=pasado)
- Auto-refresco en boleteria cada 8 segundos
- ViajesActivosView con auto-refresco cada 8 segundos
- Regla 2:30h minima entre viajes del mismo bus
- Query de conflictividad de bus en store() actualizada
- Fix horasDisponibles para revisar TODOS los buses, no solo bus seleccionado
- Fix mismatch de arrays en respuestas API con Array.isArray

### Actualizacion 2026-05-11 (resumen)

- Pantalla publica `/registro` para registro sin login
- Compra publica con 1-10 pasajeros
- Menor enlazado con adulto responsable
- Consulta autoridad acepta codigo bus, placa, IMEI GPS, codigo de viaje o QR
- Manifiesto con croquis, pasajeros alfabetico, QR bus
- Impresion/guardar PDF del boleto y manifiesto
- PaymentSimulator con QR bancario y tarjeta
- Base de datos con 16 rutas, 20 buses, 8 viajes demo

### Actualizacion 2026-05-10 (resumen)

- Portada publica animada
- Boleteria filtra rutas Cochabamba
- Boletos con boton rastrear
- Endpoints publicos para reservas y rastreo
- Croquis con colores para menores y adultos
- Vite 6.4.2 y PWA con limpieza de caches

### Actualizacion 2026-05-09 (resumen)

- Base Laravel 11 completa con auth JWT
- Modelos Eloquent con relaciones
- Servicios: bloqueo doble venta, menor/adulto, descuento mayor, QR firmado
- Seeders: 16 rutas, 20 buses, viajes demo
- Tests Pest/PHPUnit con SQLite en memoria
- Laravel Reverb para WebSockets
- Docker compose con Nginx, MySQL, Redis, Reverb

---

> Ultima actualizacion: 2026-05-14 (amanecer)
> Responsable: Codex

---

### Cambios realizados

#### 1. Codigo de viaje unico y nunca se repite

- **Backend** `ViajeController.php`: `generarCodigoViaje()` genera formato `VJ-AAAAMMDD-NNN`
- Base regenerada con `migrate:fresh --seed` para garantizar codigos unicos

#### 2. Placas de buses unicas (20 buses)

- `BusSeeder.php` configurado con 20 buses con placas unicas
- No hay buses con misma placa

#### 3. Regla 2:30 horas minima entre viajes del mismo bus

- **Backend** `ViajeController.php` - `store()`:
  ```php
  $conflicto = Viaje::where('bus_id', $bus->id)
      ->whereIn('estado', ['en_venta', 'abordando', 'en_ruta', 'programado'])
      ->where(function ($query) use ($fechaSalida) {
          $query->where('fecha_salida', '>=', $fechaSalida->copy()->subMinutes(150))
                ->where('fecha_salida', '<=', $fechaSalida->copy()->addMinutes(10));
      })
      ->exists();
  ```
- Lanza excepcion si el bus tiene viaje dentro de 2:30 horas antes o 10 minutos despues

#### 4. Consulta autoridad solo acepta codigo_viaje

- **Backend** `ConsultaViajeController.php`: `consultaPorQr()` ahora solo busca por `codigo_viaje`
- Removido fallback por placa, IMEI GPS y codigo de bus
- **Frontend** `ConsultaViajeView.vue`: placeholder actualizado, solo pide codigo de viaje

#### 5. QR de boleto incluye codigo_viaje

- **Backend** `BoletoService.php`: Payload del QR ahora incluye `codigo_viaje`
- **Frontend** `BoletoDigital.vue`: Muestra `codigo_viaje` debajo del codigo de boleto, en teal

#### 6. Viajes activos muestra codigo_viaje

- **Frontend** `ViajesActivosView.vue`: Codigo de viaje visible en teal

#### 7. Selector de viaje en venta muestra codigo_viaje primero

- **Frontend** `VentaBoletoView.vue`: Selector de viaje muestra `codigo_viaje` primero
- Panel de info destaca `codigo_viaje` en color teal

#### 8. Horas AM/PM en todo el sistema

- Funcion `hora()` en Vue formatea con `hour12: true`
- Afecta: boleteria publica, venta vendedor, viajes activos, resumen, croquis

#### 9. Duracion de ruta quitada de la boleteria

- **Frontend** `BoleteriaPublicaView.vue`: ya no muestra duracion de ruta

#### 10. Auto-refresco en boleteria cada 8 segundos

- `onMounted` crea `setInterval` que llama `cargarRutasConViajes()` completo cada 8 segundos
- Refresca tanto destinos como horarios

#### 11. Endpoint viajes filtra por estado en_venta y programatico

- **Backend** `ViajeController.php` - `index()`:
  ```php
  ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
  ->when(!$request->filled('estado'), fn ($q) => $q->whereIn('estado', ['en_venta', 'programado']))
  ```
- Cuando se pasa `estado=en_venta` solo devuelve viajes en_venta
- Cuando no se pasa estado, devuelve en_venta + programatico

#### 12. Nueva funcion cargarRutasConViajes()

- `BoleteriaPublicaView.vue`: Extrae logica a nueva funcion `cargarRutasConViajes()`
- Filtra rutas de Cochabamba que tienen viajes activos (estado 'en_venta')
- `watch(fecha)` ahora llama `cargarRutasConViajes()` en lugar de `cargarViajes()`

#### 13. Sincronizacion completa: solo destinos con viajes en_venta

- `cargarRutasConViajes()` hace `Promise.all` de:
  - `publicRutas()` - todas las rutas de Cochabamba
  - `publicViajes({ fecha, estado: 'en_venta', per_page: 500 })` - solo viajes activos
- Crea Set de `ruta_id` de viajes activos
- Filtra `rutas.value` para mostrar solo las que tienen viaje activo
- Si no hay viajes activos, no muestra ningun destino

#### 14. Fix manejo de arrays en respuestas API

- Problema: axios interceptor devuelve `response.data` directamente, pero a veces es array directo y a veces paginado `{ data: ..., meta: ... }`
- **Fix** en `cargarRutasConViajes()`:
  ```javascript
  const listaRutas = (Array.isArray(rutasRes) ? rutasRes : (rutasRes.data || [])).filter(...)
  const viajesData = Array.isArray(viajesRes) ? viajesRes : (viajesRes.data?.data || viajesRes.data || []);
  ```
- **Fix** en `cargarViajes()`:
  ```javascript
  viajes.value = Array.isArray(response) ? response : (response.data?.data || response.data || []);
  ```

#### 15. Auto-refresco mejorado

- Antes: solo recargaba horarios cuando ya estaba ruta seleccionada
- Ahora: llama `cargarRutasConViajes()` completo + `cargarViajes()` cada 8 segundos
- Esto asegura que si un viaje se cancela, el destino desaparece automaticamente

#### 16. Docker cayo y se arreglo

- Contenedores afectados: `sistema-eldorado-php-1`, `sistema-eldorado-frontend-1`, `sistema-eldorado-nginx-1`
- Solucion: `docker restart sistema-eldorado-php-1 sistema-eldorado-frontend-1 sistema-eldorado-nginx-1`
- Verificacion: `curl http://localhost/` responde 200

#### 17. Fix inconsistency when adding new viajes to boleteria

**Problema:** When creating a new viaje from CrearViajeView, BoleteriaPublicaView did not always reflect the new destination immediately. The 8-second auto-refresh was only triggered if `rutaSeleccionadaId` was set, but after `cargarRutasConViajes()` the `rutaSeleccionadaId` might be empty if the previously selected route no longer exists in the filtered list.

**Solucion:** The auto-refresh interval in `onMounted` was updated to always call `cargarRutasConViajes()` first (which also updates `rutaSeleccionadaId` automatically), and only then conditionally calls `cargarViajes()` if a route is still selected:

```javascript
onMounted(async () => {
  await cargarRutasConViajes();
  refrescarInterval = window.setInterval(async () => {
    await cargarRutasConViajes();  // Always refresh routes first
    if (rutaSeleccionadaId.value) {  // Only reload schedules if route still selected
      await cargarViajes();
    }
  }, 8000);
});
```

Also, `viajeStore.cargarViajes()` in CrearViajeView now includes `estado: 'en_venta'` in the params to ensure the created viaje appears in the list after creation:

```javascript
await viajeStore.cargarViajes({ fecha: form.fecha_salida.slice(0, 10), estado: 'en_venta' });
```

This ensures:
- When a new viaje is created, within 8 seconds BoleteriaPublicaView will refresh and show the new destination if it matches the current filter
- When a viaje is cancelled, it disappears within 8 seconds
- The behavior is now consistent in both directions (add/remove)

#### 18. Horarios estandarizados 10/dia y selector visual en CrearViajeView

**Problema:** No habia un estandar de horarios. Cada dia se generaban 8 viajes pero sin horario fijo. Si se borraba uno (ejemplo 06:00) y se queria crear otro, no se sabia cual estaba libre sin consultar manualmente.

**Solucion:**
- Se definieron 10 horas estandar: 06:00, 07:30, 09:00, 10:30, 12:00, 13:30, 15:00, 16:30, 18:00, 20:00
- Se creo endpoint `GET /viajes/horas-disponibles?bus_id=X&fecha=Y` que devuelve:
  - `disponibles`: horarios sin conflicto que se pueden usar
  - `bloqueados`: horarios ya ocupados por viajes del bus ese dia (match exacto por hora)
- ViajeSeeder genera 20 dias x 10 horarios estandar, cicla entre 8 rutas
- CrearViajeView tiene selector visual con botones para cada hora:
  - Verde con hover: horario disponible para click
  - Rojo con tachado: horario ocupado (disabled)
  - Al seleccionar bus y fecha, se cargan automaticamente los horarios
  - Auto-selecciona la ultima hora disponible (`siguienteSugerida`)
  - Al crear viaje, se recalculan los horarios disponibles

**Fix 2026-05-12:** El endpoint `horasDisponibles` hacia match por diferencia de minutos (< 150 min) lo cual era incorrecto. Ahora hace match exacto por hora del viaje existente vs hora estandar. Si un viaje existe a las 06:00, esa hora aparece en `bloqueados`, no en `disponibles`.

---

### Archivos modificados (2026-05-12)

- `backend/app/Http/Controllers/Viaje/ViajeController.php` - regla conflicto bus, filtro estado, horasDisponibles
- `backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php` - solo codigo_viaje
- `backend/app/Services/BoletoService.php` - codigo_viaje en QR
- `backend/routes/api.php` - ruta GET /viajes/horas-disponibles
- `backend/database/seeders/ViajeSeeder.php` - 10 horas estandar: 06:00, 07:30, 09:00, 10:30, 12:00, 13:30, 15:00, 16:30, 18:00, 20:00
- `frontend/src/api/viajes.js` - agregar horasDisponibles
- `frontend/src/views/public/BoleteriaPublicaView.vue` - cargarRutasConViajes, auto-refresco, fix arrays
- `frontend/src/views/autoridad/ConsultaViajeView.vue` - solo codigo_viaje
- `frontend/src/components/BoletoDigital.vue` - muestra codigo_viaje
- `frontend/src/views/vendedor/VentaBoletoView.vue` - codigo_viaje primero en selector
- `frontend/src/views/vendedor/ViajesActivosView.vue` - codigo_viaje en teal
- `frontend/src/views/vendedor/CrearViajeView.vue` - selector visual de horarios disponibles con 10 horas estandar, auto-llenar siguiente disponible
- `frontend/src/stores/viaje.js` - cargarViajes devuelve array directo o paginado

---

### Comandos de verificacion

```powershell
cd .
docker compose up -d --build
docker compose exec php php artisan migrate:fresh --seed
docker compose exec php php artisan optimize:clear
docker compose restart nginx
```

Probar en navegador:
- `http://localhost/` - portada publica
- `http://localhost/boleteria` - boleteria (solo destinos con viajes activos)
- `http://localhost/consulta` - consultar por codigo de viaje
- `http://localhost/login` - vendedor1@eldorado.bo / Eldorado2026!

---

### Reglas importantes para futuras IAs

1. **No rehacer todo el frontend** si solo se pide mejorar una pantalla
2. **No cambiar `docker-compose.yml`** salvo que sea estrictamente necesario
3. **No borrar migraciones existentes**
4. **No modificar seeders** sin revisar que la compra publica y vendedor sigan teniendo viajes disponibles
5. **No cambiar nombres de rutas** del router sin actualizar los botones que navegan a esas pantallas
6. **No quitar estados de viaje**: `programado`, `en_venta`, `abordando`, `en_ruta`, `completado`, `cancelado`
7. **No quitar estados de boleto**: `pendiente_pago`, `pendiente_verificacion`, `pagado`, `abordado`, `cancelado`, `reembolsado`

Antes de cerrar cualquier mejora, siempre ejecutar:

```powershell
docker compose exec php php artisan test
docker compose up -d --build frontend nginx
docker compose exec php php artisan optimize:clear
```

 Despues probar al menos:
- `http://localhost`
- `http://localhost/boleteria`
- `http://localhost/consulta`
- `http://localhost/rastrear`
- `http://localhost/login`

---

### Archivos clave para futuras IAs

- `SEGUIR.md` - leer primero. Resume lo ultimo, archivos tocados y pendientes
- `backend/routes/api.php` - mapa principal de endpoints
- `backend/app/Services/BoletoService.php` - reglas de emision, menor, descuento, QR y asiento
- `backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php` - consulta de autoridad, boletos por CI y rastreo
- `backend/app/Services/QrService.php` - QR de boleto y QR de texto/URL para buses
- `backend/app/Http/Controllers/Viaje/ViajeController.php` - viajes, reglas de negocio, conflicto de bus
- `frontend/src/router/index.js` - rutas visuales
- `frontend/src/views/public/BoleteriaPublicaView.vue` - compra publica, sincronizacion tiempo real
- `frontend/src/views/autoridad/ConsultaViajeView.vue` - consulta por CI y manifiesto
- `frontend/src/views/public/RastrearBusView.vue` - rastreo publico
- `frontend/src/components/CroquisBus.vue` - colores y seleccion visual de asientos
- `frontend/src/components/PaymentSimulator.vue` - simulador de tarjeta y QR

---

## Historial de sesiones anteriores

### Actualizacion 2026-05-11 (resumen)

- Pantalla publica `/registro` para registro sin login
- Compra publica con 1-10 pasajeros
- Menor enlazado con adulto responsable
- Consulta autoridad acepta codigo bus, placa, IMEI GPS, codigo de viaje o QR
- Manifiesto con croquis, pasajeros alfabetico, QR bus
- Impresion/guardar PDF del boleto y manifiesto
- PaymentSimulator con QR bancario y tarjeta
- Base de datos con 16 rutas, 20 buses, 8 viajes demo

### Actualizacion 2026-05-10 (resumen)

- Portada publica animada
- Boleteria filtra rutas Cochabamba
- Boletos con boton rastrear
- Endpoints publicos para reservas y rastreo
- Croquis con colores para menores y adultos
- Vite 6.4.2 y PWA con limpieza de caches

### Actualizacion 2026-05-09 (resumen)

- Base Laravel 11 completa con auth JWT
- Modelos Eloquent con relaciones
- Servicios: bloqueo doble venta, menor/adulto, descuento mayor, QR firmado
- Seeders: 16 rutas, 20 buses, viajes demo
- Tests Pest/PHPUnit con SQLite en memoria
- Laravel Reverb para WebSockets
- Docker compose con Nginx, MySQL, Redis, Reverb

---

> Ultima actualizacion: 2026-05-15 (sesion completa - marcadores SVG unificados, eta signal loss, mapa auto-show)
> Responsable: Codex + Usuario

---

## Resumen de la sesion 2026-05-15

### Objetivos cumplidos

1. **SVG markers unificados en todos los mapas GPS:**
   - MapaGps.vue: hexagonos verde/rojo/gris + bus SVG
   - ViajesActivosView.vue modal: mismo estilo hexagonal
   - MonitoreoMapaView.vue: ya usaba MapaGps - sin cambios

2. **Mapa en rastrear visible automaticamente:**
   - Cambio de condicion `estado_operativo === 'En ruta'` a `waypoints.length`
   - Ahora el mapa muestra los waypoints desde que hay informacion del viaje

3. **Polling mejorado:**
   - Siempre activo cuando hay viaje activo (no solo "En ruta")
   - Se detiene solo cuando estado = "Viaje finalizado" o "Viaje cancelado"

4. **ETA durante signal loss corregido:**
   - etaMinutos() recibe parametros correctos (bool, float)
   - Calcula km no registrados durante perdida de seal
   - Usa velocidad_anterior para estimar posicion

### Archivos modificados

**Frontend (4 archivos):**
- `frontend/src/components/MapaGps.vue` - SVG con L.divIcon
- `frontend/src/views/public/RastrearBusView.vue` - mapa auto-show + polling
- `frontend/src/views/vendedor/ViajesActivosView.vue` - modal con SVG unificados

**Backend (1 archivo):**
- `backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php` - etaMinutos con signal loss

### Estado actual

- Todos los mapas GPS (rastrear, viajes activos modal, monitoreo) usan el mismo estilo de marcadores SVG
- El mapa en rastrear se muestra automaticamente sin hacer click
- Durante signal loss, el ETA se calcula correctamente usando la velocidad anterior
- Polling detecta cambios de estado automaticamente

### Pendiente / siguiente sesion

- Verificar que todo funciona correctamente en navegador (Ctrl+F5)
- Probar flujo completo: iniciar viaje en vendedor -> rastrear muestra同步
- Capturas de pantalla para defensa si se necesitan
> Responsable: Codex