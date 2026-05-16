# Sistema Digital El Dorado - Terminal Cochabamba

**Version:** 1.0
**Fecha:** 2026-05-16
**Autor:** Carlos Alberto Cabezas Ramirez - UNITEPC 2026

Sistema web para gestion de pasajes, registro biometrico, control de abordaje con QR/huella y monitoreo GPS de buses de la Terminal El Dorado, Cochabamba - Bolivia.

---

## Tabla de contenidos

1. [Descripcion general](#descripcion-general)
2. [Arquitectura del sistema](#arquitectura-del-sistema)
3. [Requisitos y instalacion](#requisitos-e-instalacion)
4. [Accesos y credenciales](#accesos-y-credenciales)
5. [Modulos principales](#modulos-principales)
6. [Rutas y endpoints](#rutas-y-endpoints)
7. [Funcionalidades GPS](#funcionalidades-gps)
8. [Base de datos](#base-de-datos)
9. [Solucion de problemas](#solucion-de-problemas)
10. [Reglas para desarrollo futuro](#reglas-para-desarrollo-futuro)

---

## Descripcion general

El sistema cubre el flujo completo de una terminal de buses:

- **Registro de pasajeros** sin huella verificada ( registro publico)
- **Compra de boletos** sin login (portal publico)
- **Venta en terminal** para vendedores con croquis visual
- **Rastreo GPS** en tiempo real con simulacion
- **Consulta de autoridad** para verificacion policial
- **Control de abordaje** con QR, huella o validacion dual
- **Dashboard administrativo** con reportes y auditoria

### Caracteristicas destacadas

- Compra multiple de 1 a 10 pasajeros por transaction
- Menor de edad enlazado con adulto responsable
- QR de boleto funcional para rastreo, verificacion e impresion
- Mapa GPS con marcadores SVG profesionales
- Simulacion GPS con perdida de seal y auto-completado
- 20 buses con configuraciones de asientos unicas
- 16 rutas Cochabamba ida/vuelta a destinos de Bolivia
- 10 horarios estandar por dia (06:00 a 20:00)

---

## Arquitectura del sistema

```
┌─────────────────────────────────────────────────────────────┐
│                        Navegador                             │
│                    Vue 3 / Tailwind / PWA                    │
└─────────────────────────┬───────────────────────────────────┘
                          │ http://localhost
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                     Nginx (Docker)                            │
│              Reverse proxy + SSL (production)                 │
└──────────────┬─────────────────────────────┬────────────────┘
               │                             │
               ▼                             ▼
┌──────────────────────────┐     ┌────────────────────────────┐
│     Frontend Vue 3       │     │        Backend Laravel     │
│     Vite 6.4.2          │     │        PHP 8.2 / Laravel 11 │
│     TailwindCSS         │     │        JWT Auth             │
│     PrimeVue             │     │        MySQL 8.0           │
└──────────────────────────┘     └──────────────┬─────────────┘
                                                │
                           ┌────────────────────┼────────────────────┐
                           ▼                    ▼                    ▼
                    ┌─────────────┐      ┌─────────────┐      ┌─────────────┐
                    │    MySQL    │      │    Redis    │      │   Reverb    │
                    │   8.0       │      │   7.2       │      │  WebSocket  │
                    └─────────────┘      └─────────────┘      └─────────────┘
```

### Tecnologias

| Componente    | Tecnologia              | Version     |
|---------------|-------------------------|-------------|
| Frontend      | Vue 3 + Composition API | 3.5.x       |
| Build tool    | Vite                    | 6.4.x       |
| CSS           | TailwindCSS             | 3.4.x       |
| UI Components | PrimeVue                | 4.x         |
| State         | Pinia                   | 2.x         |
| Backend       | Laravel                 | 11.x        |
| PHP           | PHP                      | 8.2         |
| Database      | MySQL                    | 8.0         |
| Cache/Queue   | Redis                    | 7.2         |
| WebSocket     | Laravel Reverb          | 1.x         |
| Maps          | Leaflet                  | 1.9.x       |
| Icons         | Lucide                   | latest      |

---

## Requisitos e instalacion

### Requisitos

- Docker Desktop 4.x o superior
- Git
- Navegador moderno (Chrome, Firefox, Edge)
- 8GB RAM minimo para Docker
- Puerto 80 libre

### Instalacion rapida

```powershell
# 1. Clonar o copiar la carpeta del proyecto

# 2. Levantar contenedores
docker compose up -d --build

# 3. Crear base de datos y datos demo
docker compose exec php php artisan migrate:fresh --seed

# 4. Limpiar cache
docker compose exec php php artisan optimize:clear

# 5. Abrir en navegador
http://localhost
```

### Arranque diario

```powershell
# Si Docker Desktop esta cerrado, abrirlo primero y esperar 15-30 segundos

docker compose up -d
docker compose exec php php artisan optimize:clear
```

### Si hubo cambios en codigo

```powershell
# Reconstruir contenedores
docker compose up -d --build

# Limpiar cache Laravel
docker compose exec php php artisan optimize:clear

# Reiniciar nginx si hay errores 502
docker compose restart nginx
```

### Reiniciar base desde cero

```powershell
# ATENCION: Esto borra todos los datos
docker compose exec php php artisan migrate:fresh --seed
```

---

## Accesos y credenciales

### URLs del sistema

| Servicio                  | URL                              |
|---------------------------|----------------------------------|
| Portada publica           | http://localhost                 |
| Registro publico          | http://localhost/registro        |
| Boleteria                 | http://localhost/boleteria        |
| Rastreo GPS               | http://localhost/rastrear        |
| Consulta autoridad        | http://localhost/consulta        |
| Login                     | http://localhost/login           |
| Venta vendedor            | http://localhost/venta           |
| Viajes activos            | http://localhost/viajes-activos  |
| Crear viaje               | http://localhost/viajes/crear    |
| Monitoreo GPS             | http://localhost/monitoreo-gps   |
| Control abordaje          | http://localhost/abordaje        |
| Dashboard                 | http://localhost/dashboard       |
| API base                  | http://localhost/api             |
| Reverb WebSocket          | ws://localhost:8080/app/eldorado-key |

### Usuarios demo

| Rol          | Email                   | Contrasena        |
|--------------|-------------------------|-------------------|
| Administrador| admin@eldorado.bo       | Eldorado2026!     |
| Supervisor   | supervisor@eldorado.bo   | Eldorado2026!     |
| Vendedor     | vendedor1@eldorado.bo    | Eldorado2026!     |
| Auxiliar     | auxiliar1@eldorado.bo    | Eldorado2026!     |

---

## Modulos principales

### 1. Portal publico (sin login)

**Registro publico** (`/registro`)
- Llenar: nombres, apellidos, CI, expedido, fecha nacimiento, telefono, correo opcional
- Queda con estado `Huella no verificada`
- Disponible para compra y venta en terminal

**Compra de boletos** (`/boleteria`)
- Seleccionar fecha, destino y horario
- Croquis de bus con seleccion visual de asientos
- Buscar pasajero por CI o pre-registrar
- Compra multiple de 1 a 10 pasajeros
- Menor debe enlazarse con adulto de la misma compra
- Metodos de pago: efectivo, tarjeta, QR bancario
- Genera boletos con QR y enlace de rastreo

**Rastreo GPS** (`/rastrear`)
- Ingresar: codigo de bus, boleto, viaje, placa o IMEI GPS
- Muestra estado, progreso, velocidad y ETA
- Mapa con marcadores SVG profesionales
- Auto-refresco cada 5 segundos

**Consulta** (`/consulta`)
- Por CI: muestra viajes comprados, asiento, bus, salida, estado, total
- Por codigo de viaje (autoridad): manifiesto completo con croquis y pasajeros

### 2. Vendedor (login requerido)

**Venta en terminal** (`/venta`)
- Seleccionar fecha, destino y horario
- Croquis visual del bus
- Buscar/registrar pasajeros
- Validar cada pasajero antes de asignar asiento
- Metodo de pago: efectivo, tarjeta, QR

**Registro de pasajeros** (`/pasajeros`)
- Ver todos los pasajeros registrados
- Filtrar por: todos, verificados, no verificados
- Registrar huella simulada

**Viajes activos** (`/viajes-activos`)
- Ver viajes en estado en_venta, abordando, en_ruta
- Crear nuevos viajes
- Iniciar viaje -> abre monitoreo GPS
- Finalizar o cancelar viajes

**Crear viaje** (`/viajes/crear`)
- Seleccionar ruta, bus, fecha y horario
- Horarios estandar: 06:00, 07:30, 09:00, 10:30, 12:00, 13:30, 15:00, 16:30, 18:00, 20:00
- Validacion: 2:30h minima entre viajes del mismo bus
- Selector visual de horarios disponibles

### 3. Control de abordaje

**Abordaje** (`/abordaje`)
- Escanear QR, ingresar codigo o buscar por CI
- Validacion: QR, huella o dual (QR + huella)
- Cambia estado del boleto a `abordado`

### 4. GPS y monitoreo

**Monitoreo GPS** (`/monitoreo-gps?viaje={id}`)
- Solo accesible desde Viajes activos al iniciar un viaje
- Bus se mueve por la ruta en tiempo real
- Polling cada 2 segundos
- Muestra: progreso, ETA, velocidad, waypoint actual
- Marcadores SVG unificados con el resto del sistema

**Simulacion GPS**
- 8 rutas con waypoints reales: La Paz, Santa Cruz, Oruro, Potosi, Sucre, Tarija, Trinidad, Cobija
- Duracion basada en distancia (70 km/h promedio)
- 12.5% probabilidad de perdida de seal por 5-15 segundos
- Auto-completado cuando el bus llega a destino
- Linea punteada en mapa durante signal loss

### 5. Administracion

**Dashboard** (`/dashboard`)
- Estadisticas generales
- Viajes del dia
- Pasajeros y ventas

**Reportes**
- Ventas por ruta
- Ocupacion de buses
- Ingresos

**Auditoria**
- Log de todos los eventos del sistema

---

## Rutas y endpoints

### Endpoints publicos

| Metodo | Endpoint                              | Descripcion                    |
|--------|---------------------------------------|--------------------------------|
| GET   | /api/public/rutas                     | Lista de rutas activas         |
| GET   | /api/public/viajes                    | Viajes por fecha               |
| GET   | /api/public/viajes/{id}/asientos      | Asientos disponibles           |
| POST  | /api/public/pasajeros/pre-registro    | Pre-registro de pasajero       |
| POST  | /api/public/boletos/reservar           | Reservar asientos              |
| GET   | /api/public/rastreo/{codigo}          | Rastreo por codigo             |
| GET   | /api/consulta/cliente/{ci}/boletos    | Boletos por CI                |
| GET   | /api/consulta/boleto/{codigo}         | Detalle de boleto             |
| GET   | /api/consulta/viaje/{codigo}          | Consulta por codigo de viaje   |
| GET   | /api/consulta/viaje/{id}/manifiesto   | Manifiesto completo           |

### Endpoints GPS

| Metodo | Endpoint                              | Descripcion                    |
|--------|---------------------------------------|--------------------------------|
| POST  | /api/gps/viaje/{id}/iniciar          | Iniciar simulacion GPS        |
| POST  | /api/gps/viaje/{id}/avanzar          | Avanzar simulacion 2s         |
| GET   | /api/gps/viaje/{id}/estado           | Estado actual de simulacion   |

### Endpoints privados (requiere auth)

| Metodo | Endpoint                              | Descripcion                    |
|--------|---------------------------------------|--------------------------------|
| POST  | /api/auth/login                       | Login JWT                     |
| POST  | /api/auth/logout                      | Logout                        |
| GET   | /api/viajes                           | Lista de viajes               |
| POST  | /api/viajes                           | Crear viaje                   |
| GET   | /api/viajes/horas-disponibles         | Horarios disponibles           |
| GET   | /api/pasajeros                        | Lista de pasajeros            |
| POST  | /api/abordaje/validar                 | Validar abordaje               |

---

## Funcionalidades GPS

### Como iniciar un viaje GPS

1. Ir a `/viajes-activos` como vendedor
2. Seleccionar un viaje en estado `en_venta` o `abordando`
3. Click en **"Iniciar viaje"**
4. El viaje cambia a `en_ruta` y se abre el monitoreo en `/monitoreo-gps?viaje={id}`

### Como rastrear un bus

1. Ir a `/rastrear`
2. Ingresar codigo de viaje, bus, placa, codigo de boleto o IMEI GPS
3. Ver estado, mapa con marcadores SVG y datos del viaje
4. El mapa se muestra automaticamente (no requiere click)
5. Auto-refresco cada 5 segundos

### Marcadores SVG unificados

Todos los mapas GPS usan el mismo estilo de marcadores:

| Marcador           | Forma       | Color   | Descripcion                  |
|--------------------|-------------|---------|-------------------------------|
| Punto de partida   | Hexagono    | Verde   | Cruz blanca dentro            |
| Punto de llegada   | Hexagono    | Rojo    | Rectangulo blanco dentro      |
| Waypoints intermedios | Circulo | Gris    | Numero de posicion            |
| Bus                | Bus estilizado | Teal | Progreso (%) o "Sin seal"     |

### Perdida de seal GPS (signal loss)

- 12.5% probabilidad cada vez que se llama avanzar
- Duracion: 5-15 segundos random
- Durante signal loss:
  - Velocidad muestra "~XX km/h" (velocidad anterior)
  - Linea punteada en el mapa
  - ETA sigue calculando correctamente
  - Badge "Sin seal" visible en el bus

### Auto-completado

- Cuando el progreso llega a 100%, el viaje se marca como `completado`
- `fecha_llegada_real` se setea automaticamente
- El viaje desaparece de la lista de viajes activos

### Rutas disponibles

| Codigo | Destino     | Distancia aprox | Duracion aprox |
|--------|-------------|-----------------|----------------|
| CBB-LPZ | La Paz      | 230 km          | 3.3 horas      |
| CBB-SCZ | Santa Cruz  | 460 km          | 6.6 horas      |
| CBB-ORU | Oruro       | 120 km          | 1.7 horas      |
| CBB-PTS | Potosi      | 380 km          | 5.4 horas      |
| CBB-SRE | Sucre       | 290 km          | 4.1 horas      |
| CBB-TJA | Tarija      | 520 km          | 7.4 horas      |
| CBB-TDD | Trinidad    | 400 km          | 5.7 horas      |
| CBB-CIJ | Cobija      | 650 km          | 9.3 horas      |

---

## Base de datos

### Estructura de tablas principales

```
usuarios
  - id, nombre, email, password, rol, remember_token, email_verified_at

pasajeros
  - id, nombres, apellidos, ci, expedido, fecha_nacimiento, telefono, email
  - huella_verificada, fecha_registro

huellas_dactilares
  - id, pasajero_id, template_simulado, fecha_registro

menores_adultos_responsables
  - id, menor_id, adulto_id, boleto_id

rutas
  - id, nombre, origen, destino, codigo, precio_base, duracion_estimada

buses
  - id, numero_bus, placa, marca, modelo, capacidad, estado, gps_imei

viajes
  - id, ruta_id, bus_id, codigo, fecha_salida, hora_salida, estado
  - precio, simulacion_llamada_actual, simulacion_llamadas_totales

asientos
  - id, viaje_id, numero, fila, columna, estado, tipo

boletos
  - id, viaje_id, pasajero_id, asiento_id, codigo_qr, estado, precio
  - fecha_compra, metodo_pago, codigo_viaje

ubicaciones_gps
  - id, viaje_id, lat, lng, velocidad, seal_perdida, timestamp

eventos_abordaje
  - id, boleto_id, tipo, datos, timestamp

audit_logs
  - id, usuario_id, accion, modelo, modelo_id, datos, ip, timestamp
```

### Estados de viaje

| Estado      | Descripcion                          | Puede iniciar GPS |
|-------------|--------------------------------------|-------------------|
| en_venta    | Venta de boletos activa              | Si                |
| abordando   | Abordaje de pasajeros                | Si                |
| en_ruta     | Viaje en curso con GPS activo        | No                |
| completado  | Viaje finalizado                     | No                |
| cancelado   | Viaje cancelado                      | No                |

### Estados de boleto

| Estado              | Descripcion                    |
|---------------------|--------------------------------|
| pendiente_pago      | Esperando pago                 |
| pendiente_verificacion | Esperando verificacion      |
| pagado              | Pagado, listo para abordar     |
| abordado            | Pasajero abordo el bus          |
| cancelado           | Boleto cancelado               |
| reembolsado         | Reembolso procesado            |

---

## Solucion de problemas

### Docker no responde

```
Error: failed to connect to the docker API at npipe:////./pipe/dockerDesktopLinuxEngine
```

**Solucion:**
1. Abrir Docker Desktop
2. Esperar 15-30 segundos
3. Verificar con: `docker version`

### Puerto 80 ocupado

```
Error: Ports are not available
```

**Solucion:**
1. Cerrar Apache/XAMPP/IIS u otro servidor en puerto 80
2. O cambiar puerto en docker-compose.yml

### 502 Bad Gateway

**Solucion:**
```powershell
docker compose restart nginx
docker compose exec php php artisan optimize:clear
```

### Frontend muestra version vieja

**Solucion:**
1. `Ctrl + F5`
2. DevTools > Application > Service Workers > Unregister
3. Recargar

### Base de datos vacia

**Solucion:**
```powershell
docker compose exec php php artisan migrate --seed
```

### Datos demo desordenados

**Solucion:**
```powershell
docker compose exec php php artisan migrate:fresh --seed
```

### No se ven cambios en el mapa

**Solucion:**
```powershell
docker compose up -d --build
docker compose exec php php artisan optimize:clear
```

---

## Reglas para desarrollo futuro

### IMPORTANTE - No romper el sistema

1. **No rehacer todo el frontend** si solo se pide mejorar una pantalla
2. **No cambiar docker-compose.yml** salvo que sea estrictamente necesario
3. **No borrar migraciones existentes**
4. **No modificar seeders** sin revisar que la compra publica y vendedor sigan teniendo viajes disponibles
5. **No cambiar nombres de rutas del router** sin actualizar los botones que navegan a esas pantallas
6. **No quitar estados de viaje:** en_venta, abordando, en_ruta, completado, cancelado
7. **No quitar estados de boleto:** pendiente_pago, pendiente_verificacion, pagado, abordado, cancelado, reembolsado

### Antes de cerrar cualquier mejora

Ejecutar siempre:

```powershell
docker compose exec php php artisan test
docker compose up -d --build frontend nginx
docker compose exec php php artisan optimize:clear
```

Despues probar:
- http://localhost
- http://localhost/boleteria
- http://localhost/consulta
- http://localhost/rastrear
- http://localhost/login

### Archivos clave para futuras IAs

| Archivo | Purpose |
|---------|---------|
| SEGUIR.md | Leer primero - resume ultimos cambios y archivos tocados |
| backend/routes/api.php | Mapa de endpoints |
| backend/app/Http/Controllers/GPS/GpsController.php | Simulacion GPS |
| backend/app/Http/Controllers/Viaje/ViajeController.php | Reglas de negocio de viajes |
| backend/app/Services/BoletoService.php | Emision de boletos y QR |
| backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php | Consulta de autoridad |
| backend/app/Services/QrService.php | Generacion de QR |
| frontend/src/router/index.js | Rutas del frontend |
| frontend/src/components/MapaGps.vue | Mapa GPS con marcadores SVG |
| frontend/src/components/CroquisBus.vue | Croquis de asientos |
| frontend/src/components/PaymentSimulator.vue | Simulador de pago |
| frontend/src/views/public/BoleteriaPublicaView.vue | Compra publica |
| frontend/src/views/public/RastrearBusView.vue | Rastreo GPS publico |

---

## Estado actual del proyecto

### Funcionalidades implementadas

- Registro publico sin huella verificada
- Compra publica de boletos sin login
- Sincronizacion tiempo real 1:1 entre ViajesActivos y Boleteria
- Compra multiple de 1 a 10 pasajeros
- Menor enlazado con adulto responsable
- Venta por vendedor con croquis visual
- Codigo de viaje unico VJ-AAAAMMDD-NNN
- 10 horarios estandar: 06:00, 07:30, 09:00, 10:30, 12:00, 13:30, 15:00, 16:30, 18:00, 20:00
- Selector visual de horarios en CrearViaje
- Regla de conflicto de bus: 2:30h minima entre viajes
- QR de boleto con codigo_viaje incluido
- Horas AM/PM en todo el sistema
- Consulta autoridad por codigo de viaje
- Consulta pasajeros por CI
- Impresion/guardar PDF de boleto y manifiesto
- Rastreo por codigo boleto, viaje, placa o IMEI GPS
- Mapa GPS con marcadores SVG profesionales
- Mapa en rastrear visible automaticamente
- ETA durante signal loss calculado correctamente
- Croquis con colores: adulto, adulto mayor, adulto con menor, menor
- Simulador de pago: efectivo, tarjeta, QR bancario
- Control abordaje por QR, huella o validacion dual
- GPS monitoreo en tiempo real con simulacion
- 8 rutas con waypoints reales
- Marcadores SVG unificados en todos los mapas
- Perdida de seal GPS con linea punteada
- Auto-completacion de viaje
- Dashboard, reportes y auditoria
- 20 buses con placas unicas
- 16 rutas Cochabamba ida/vuelta
- Datos demo: 20 dias x 10 horarios = 200 viajes

### Pendiente / siguiente sesion

- Capturas finales para la defensa
- Ampliar Swagger con ejemplos completos
- Probar en dos navegadores los eventos Reverb en tiempo real
- Ajustar textos finales de marca/logo segun el nombre oficial
- Crear repositorio GitHub para backup

---

**Documento actualizado:** 2026-05-16
**Responsable:** Carlos Alberto Cabezas Ramirez