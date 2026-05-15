# Sistema Digital El Dorado - Terminal Cochabamba

Sistema web para gestion de pasajes, registro biometrico, control de abordaje con QR/huella y monitoreo GPS de buses de la Terminal El Dorado, Cochabamba - Bolivia.

Proyecto de grado: Carlos Alberto Cabezas Ramirez - UNITEPC 2026.

## Que incluye

- Portal publico para comprar boletos sin iniciar sesion.
- Portada publica con acceso a compra, rastreo y consulta.
- Registro publico de pasajeros sin huella verificada.
- Busqueda de boletos por carnet desde consulta publica.
- QR de boleto funcional para abordaje, verificacion, rastreo e impresion.
- QR/codigo por bus para consulta policial, croquis completo y rastreo.
- Impresion o guardado como PDF desde el dialogo del navegador.
- Registro de pasajeros con huella simulada y validacion de duplicados.
- Venta en terminal para vendedor, con croquis visual de bus.
- Compra multiple de 1 a 10 pasajeros.
- Enlace de menor de edad con adulto responsable.
- Colores de croquis: adulto, adulto mayor, adulto con menor y menor.
- Simulador de pago con tarjeta y QR bancario antes de emitir la compra.
- Creacion de viajes con rutas Cochabamba ida/vuelta a departamentos de Bolivia.
- Catalogo de 20 buses con croquis segun capacidad.
- Panel de viajes activos con finalizar o cancelar viaje.
- Control de abordaje por QR, huella o validacion dual.
- Monitoreo GPS y rastreo publico por codigo de boleto, viaje o bus.
- Consulta de manifiesto para autoridades.
- Dashboard, usuarios, rutas, buses, reportes y auditoria.

## Requisitos

- Docker Desktop 4.x o superior.
- Git.
- Navegador moderno.
- Node.js 20+ solo si se quiere ejecutar el frontend fuera de Docker.

## Que se entrega si alguien pide el proyecto

Se debe entregar la carpeta completa del proyecto, no solamente este archivo `README.md`.

La carpeta debe conservar:

- `docker-compose.yml`: levanta Nginx, PHP, MySQL, Redis, frontend, Reverb, queue y scheduler.
- `backend/`: API Laravel, migraciones, seeders, servicios, controladores y tests.
- `frontend/`: aplicacion Vue, pantallas, componentes y estilos.
- `nginx/`: configuracion para servir frontend y API.
- `.env.example`: variables base de desarrollo.
- `README.md`: guia de uso.
- `SEGUIR.md`: bitacora tecnica para continuar el desarrollo.

No es obligatorio copiar los volumenes internos de Docker. En otra PC se recrean con `migrate --seed`.

## Instalacion rapida

Desde PowerShell:

```powershell
cd .
docker compose up -d --build
docker compose exec php php artisan migrate --seed
docker compose exec php php artisan optimize:clear
```

Si Docker Desktop esta cerrado, abrirlo primero y esperar a que el motor inicie.

## Primera ejecucion en otra PC

1. Instalar Docker Desktop.
2. Abrir Docker Desktop y esperar que diga que el motor esta corriendo.
3. Copiar la carpeta completa del proyecto.
4. Abrir PowerShell dentro de la carpeta.
5. Ejecutar:

```powershell
docker compose up -d --build
docker compose exec php php artisan migrate --seed
docker compose exec php php artisan optimize:clear
```

6. Abrir `http://localhost`.

Si ya existia una base anterior y se quiere dejar la demo limpia:

```powershell
docker compose exec php php artisan migrate:fresh --seed
```

Advertencia: `migrate:fresh --seed` borra datos existentes y vuelve a crear datos demo.

## Arranque diario

Cuando la PC se apaga, normalmente solo se necesita:

```powershell
cd .
docker compose up -d
docker compose exec php php artisan optimize:clear
```

Si se hicieron cambios de frontend/backend:

```powershell
docker compose up -d --build
docker compose exec php php artisan optimize:clear
docker compose restart nginx
```

`docker compose restart nginx` ayuda cuando Nginx queda apuntando a una IP vieja de PHP despues de reconstruir contenedores.

## Accesos

- Portada publica: http://localhost
- Registro publico: http://localhost/registro
- Comprar boletos: http://localhost/boleteria
- Rastrear bus: http://localhost/rastrear
- Consulta autoridad/pasajero: http://localhost/consulta
- Login: http://localhost/login
- API base: http://localhost/api
- Reverb WebSocket: ws://localhost:8080/app/eldorado-key

Usuarios demo:

- Administrador: `admin@eldorado.bo / Eldorado2026!`
- Supervisor: `supervisor@eldorado.bo / Eldorado2026!`
- Vendedor: `vendedor1@eldorado.bo / Eldorado2026!`
- Auxiliar: `auxiliar1@eldorado.bo / Eldorado2026!`

## Flujos principales

### Portal publico

1. Entrar a `http://localhost`.
2. Si el pasajero no existe, puede entrar a `Registrarse` y guardar sus datos.
3. El registro publico queda con estado `Huella no verificada`.
4. Pulsar `Comprar boletos`.
5. Seleccionar fecha, destino y horario.
6. El sistema carga el croquis del bus automaticamente.
7. Buscar pasajero por carnet.
8. Si no existe, permite pre-registro publico con huella pendiente.
9. Elegir cantidad de pasajeros de 1 a 10.
10. Si un pasajero es menor, debe enlazarse con un adulto de la misma compra.
11. Seleccionar asientos en el croquis.
12. Elegir metodo de pago:
    - Efectivo: confirma directo.
    - Tarjeta: abre simulador, procesa tarjeta y genera recibo.
    - QR bancario: muestra QR simulado, contador de 10 minutos y verificacion.
13. Se generan boletos con QR y enlace de rastreo.

### Registro publico

1. Entrar a `http://localhost/registro`.
2. Llenar nombres, apellidos, CI, expedido, fecha de nacimiento, telefono y correo opcional.
3. Guardar registro.
4. El pasajero queda disponible para compra publica y venta en terminal.
5. En la lista del vendedor aparece como `Huella no verificada`.
6. Para cambiar a verificado, el pasajero debe pasar por plataforma y registrar huella en el panel vendedor.

### Compra publica

1. Entrar a `http://localhost/boleteria`.
2. Seleccionar fecha, destino y horario.
3. El sistema carga el croquis del bus automaticamente.
4. Buscar pasajero por carnet.
5. Si no existe, permite pre-registro publico con huella pendiente.
6. Elegir cantidad de pasajeros de 1 a 10.
7. Si un pasajero es menor, debe enlazarse con un adulto de la misma compra.
8. Seleccionar asientos en el croquis.
9. Elegir metodo de pago:
    - Efectivo: confirma directo.
    - Tarjeta: abre simulador, procesa tarjeta y genera recibo.
    - QR bancario: muestra QR simulado, contador de 10 minutos y verificacion.
10. Se generan boletos con QR y enlace de rastreo.

### Consulta de pasajero

1. Entrar a `http://localhost/consulta`.
2. En `Buscar boletos por carnet`, escribir el CI.
3. El sistema muestra: viajes comprados, asiento, bus, salida, estado, QR y total.
4. Botones disponibles:
   - `Rastrear bus`.
   - `Imprimir / guardar PDF`.
5. Para guardar PDF, usar el dialogo del navegador y elegir `Guardar como PDF`.

### Consulta de autoridad

1. Entrar a `http://localhost/consulta`.
2. Escribir codigo de bus, placa, IMEI GPS, codigo de viaje o QR.
3. Ver datos del bus, codigo operativo, QR del bus y ruta.
4. Ver croquis completo con asientos ocupados y nombre/CI del pasajero en tooltip.
5. Ver lista alfabetica de pasajeros del bus.
6. Rastrear el bus con el mismo codigo.
7. Imprimir o guardar PDF como constancia policial.

### Vendedor

1. Entrar a `http://localhost/login`.
2. Usar `vendedor1@eldorado.bo / Eldorado2026!`.
3. Opciones:
   - Emitir boletos en terminal.
   - Registrar pasajeros y huellas simuladas.
   - Ver pasajeros registrados con filtros.
   - Ver viajes activos.
   - Agregar viajes.
4. En venta de boleto, `2. Pasajeros` permite elegir de 1 a 10 pasajeros.
5. Segun la cantidad elegida aparecen tarjetas numeradas para poner carnet y validar cada pasajero.
6. El vendedor selecciona un pasajero activo y luego elige su asiento en el croquis.
7. Si un pasajero es menor, se enlaza con un adulto de la misma compra.
8. El asiento solo se confirma como vendido al emitir la compra.

### Viajes y buses

- Rutas base desde Cochabamba a La Paz, Santa Cruz, Oruro, Potosi, Sucre, Tarija, Trinidad y Cobija.
- Rutas de ida y vuelta activas.
- 20 buses demo con configuracion de asientos.
- Crear viaje permite seleccionar ruta, bus, fecha, horario y precio.
- El croquis se genera segun el bus seleccionado.
- En viajes activos se puede finalizar o cancelar viajes antiguos.

### Rastreo GPS

1. Entrar a `http://localhost/rastrear`.
2. Escribir codigo de bus, codigo de boleto, codigo de viaje, placa o IMEI GPS.
3. El sistema muestra estado:
   - Aun no partio.
   - En abordaje.
   - En ruta.
   - Viaje finalizado.
   - Cancelado.
4. Muestra mapa con pines SVG profesionales, posicion simulada o ultima ubicacion GPS, velocidad y tiempo estimado.
5. El mapa muestra automaticamente los waypoints de la ruta desde que hay informacion del viaje.
6. Durante signal loss muestra velocidad estimada (km/h antes de perder seal).

### Monitoreo GPS en tiempo real (simulacion)

1. Entrar a `http://localhost/login` como vendedor.
2. Ir a Viajes Activos.
3. Crear o seleccionar un viaje en estado `en_venta` o `abordando`.
4. Click en **"Iniciar viaje"** -> el viaje cambia a `en_ruta`.
5. Se abre el mapa de monitoreo en `http://localhost/monitoreo-gps?viaje={id}`.
6. El bus se mueve por la ruta en tiempo real (polling cada 2 segundos).
7. Se muestra: progreso (%), ETA (minutos restantes), velocidad (km/h), waypoint actual.
8. **Perdida de seal GPS**: cada ~8 llamadas hay 12.5% probabilidad de que el bus "pierda cobertura" por 5-15 segundos.
9. **Auto-completacion**: despues de ~1 minuto (simulacion), el viaje se marca como `completado` automaticamente con `fecha_llegada_real`.
10. **Pines y marcadores SVG**: todos los mapas GPS (Monitoreo, ViajesActivos modal, Rastrear) usan el mismo estilo de marcadores:
    - Inicio: hexagono verde con flecha
    - Fin: hexagono rojo con banderin
    - Intermedios: circulo gris
    - Bus: SVG de bus estilizado con progreso (%) o "Sin seal"
11. **ETA durante signal loss**: cuando el GPS pierde seal, el sistema usa la velocidad anterior para estimar la posicion y continuar calculando el ETA correctamente.

## Arquitectura

```text
Navegador Vue/PWA
      |
      v
Nginx reverse proxy
      |
      +--> Frontend Vue 3 / Vite / Tailwind / PrimeVue
      |
      +--> Laravel 11 API REST
               |
               +--> MySQL 8.0
               +--> Redis 7.2
               +--> Laravel Reverb WebSockets
               +--> Queue worker
               +--> Scheduler
```

## Backend

Tecnologias:

- PHP 8.2.
- Laravel 11.
- Eloquent ORM.
- JWT auth.
- MySQL 8.0.
- Redis.
- Laravel Reverb.
- QR firmado.
- Huella simulada cifrada.

Modulos:

- Auth y 2FA simulado.
- Pasajeros.
- Huellas dactilares.
- Menores y adulto responsable.
- Rutas.
- Buses.
- Viajes.
- Asientos.
- Boletos.
- Abordaje.
- GPS.
- Reportes.
- Auditoria.
- Consulta publica.

Endpoints publicos importantes:

- `GET /api/public/rutas`
- `GET /api/public/viajes`
- `GET /api/public/viajes/{id}/asientos`
- `POST /api/public/pasajeros/pre-registro`
- `POST /api/public/boletos/reservar`
- `GET /api/public/rastreo/{codigo}`
- `GET /api/consulta/cliente/{ci}/boletos`
- `GET /api/consulta/boleto/{codigo}`
- `GET /api/consulta/viaje/{codigo_qr}`
- `GET /api/consulta/viaje/{id}/manifiesto`

Notas de consulta:

- El codigo de bus usado para rastreo/consulta es `gps_imei` si existe; si no existe, se usa `placa`.
- El QR del bus abre `http://localhost/consulta?codigo=CODIGO_BUS`.
- Al consultar por codigo de bus, el backend busca el viaje registrado mas relevante del bus.
- Prioridad de viaje por bus: `en_ruta`, `abordando`, `en_venta`, `programado`.
- Esto permite que un viaje ya creado/registrado se pueda consultar aunque todavia no haya partido.
- Si el bus tiene varios viajes registrados, se toma el mas reciente segun esa prioridad y fecha de salida.
- El resultado devuelve manifiesto, croquis completo, QR del bus y pasajeros ordenados alfabeticamente.

## Frontend

Tecnologias:

- Vue 3.
- Composition API.
- Vite.
- TailwindCSS.
- PrimeVue.
- Pinia.
- Vue Router.
- Axios.
- Leaflet.
- PWA.
- Lucide icons.

Pantallas:

- `HomePublicaView`: portada publica animada.
- `RegistroPublicoView`: registro publico sin huella verificada.
- `BoleteriaPublicaView`: compra publica de boletos.
- `RastrearBusView`: rastreo publico.
- `ConsultaViajeView`: autoridad y pasajeros por CI.
- `LoginView`: acceso por rol.
- `VentaBoletoView`: venta en terminal.
- `RegistroPasajeroView`: pasajeros y huellas.
- `ViajesActivosView`: viajes activos.
- `CrearViajeView`: crear viaje.
- `ControlAbordajeView`: abordaje.
- `MonitoreoMapaView`: GPS.
- Dashboard y administracion.

## Comandos utiles

Levantar todo:

```powershell
docker compose up -d --build
```

Ver estado:

```powershell
docker compose ps
```

Limpiar cache Laravel:

```powershell
docker compose exec php php artisan optimize:clear
```

Migrar y sembrar base:

```powershell
docker compose exec php php artisan migrate --seed
```

Reiniciar base desde cero:

```powershell
docker compose exec php php artisan migrate:fresh --seed
```

Ejecutar tests:

```powershell
docker compose exec php php artisan test
```

Ver logs frontend:

```powershell
docker compose logs --tail 100 frontend
```

Ver logs backend:

```powershell
docker compose logs --tail 100 php
```

Apagar:

```powershell
docker compose down
```

## Si no se ven cambios

El sistema es PWA y el navegador puede guardar cache.

1. Presionar `Ctrl + F5`.
2. Si sigue igual, abrir DevTools.
3. Ir a Application.
4. Service Workers.
5. Pulsar unregister.
6. Recargar `http://localhost`.

## Solucion de problemas comunes

### Docker no responde

Error tipico:

```text
failed to connect to the docker API at npipe:////./pipe/dockerDesktopLinuxEngine
```

Solucion:

1. Abrir Docker Desktop.
2. Esperar 15 a 30 segundos.
3. Probar:

```powershell
docker version
docker compose up -d
```

### Puerto 80 ocupado

Si `http://localhost` no abre o Docker dice que el puerto 80 esta ocupado, cerrar Apache/XAMPP/IIS u otro servidor local. Este proyecto usa Nginx en Docker, no necesita XAMPP.

### 502 Bad Gateway

Puede pasar despues de reconstruir PHP. Solucion:

```powershell
docker compose restart nginx
docker compose exec php php artisan optimize:clear
```

### Frontend viejo por cache

Presionar `Ctrl + F5`. Si sigue viejo, borrar el Service Worker de la PWA desde DevTools > Application > Service Workers > unregister.

### Base de datos vacia

Ejecutar:

```powershell
docker compose exec php php artisan migrate --seed
```

### Datos demo desordenados

Si se probaron muchas compras y se quiere volver a la demo inicial:

```powershell
docker compose exec php php artisan migrate:fresh --seed
```

## Reglas para futuras mejoras

Para evitar romper el sistema completo:

- No rehacer todo el frontend si solo se pide mejorar una pantalla.
- No cambiar `docker-compose.yml` salvo que sea estrictamente necesario.
- No borrar migraciones existentes.
- No modificar seeders sin revisar que la compra publica y vendedor sigan teniendo viajes disponibles.
- No cambiar nombres de rutas del router sin actualizar los botones que navegan a esas pantallas.
- No quitar estados de boleto: `pendiente_pago`, `pendiente_verificacion`, `pagado`, `abordado`, `cancelado`, `reembolsado`.
- No quitar estados de viaje: `en_venta`, `abordando`, `en_ruta`, `completado`, `cancelado`. (El estado 'programado' YA FUE REMOVIDO del sistema).
- Antes de cerrar cualquier mejora, ejecutar:

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

## Archivos clave para futuras IAs

- `SEGUIR.md`: leer primero. Resume lo ultimo, archivos tocados y pendientes.
- `backend/routes/api.php`: mapa principal de endpoints.
- `backend/app/Http/Controllers/GPS/GpsController.php`: simulacion GPS, waypoints, signal loss, auto-completar viaje.
- `backend/app/Http/Controllers/Viaje/ViajeController.php`: viajes, reglas de negocio, conflicto de bus 2:30h.
- `backend/app/Services/BoletoService.php`: reglas de emision, menor, descuento, QR y asiento.
- `backend/app/Http/Controllers/Autoridad/ConsultaViajeController.php`: consulta de autoridad, boletos por CI y rastreo.
- `backend/app/Services/QrService.php`: QR de boleto y QR de texto/URL para buses.
- `frontend/src/router/index.js`: rutas visuales.
- `frontend/src/views/vendedor/MonitoreoMapaView.vue`: monitoreo GPS con polling cada 2s, waypoints, bus en movimiento.
- `frontend/src/views/public/BoleteriaPublicaView.vue`: compra publica, sincronizacion tiempo real con viajes activos.
- `frontend/src/views/autoridad/ConsultaViajeView.vue`: consulta por CI y manifiesto.
- `frontend/src/views/public/RastrearBusView.vue`: rastreo publico.
- `frontend/src/components/MapaGps.vue`: mapa Leaflet con marcadores de partida, llegada y bus.
- `frontend/src/components/CroquisBus.vue`: colores y seleccion visual de asientos.
- `frontend/src/components/PaymentSimulator.vue`: simulador de tarjeta y QR.

## Estado del proyecto

El sistema ya corre con Docker y cubre el flujo principal:

- Registro publico sin huella verificada.
- Compra publica de boletos sin login.
- Sincronizacion tiempo real 1:1 entre ViajesActivos y Boleteria: cancelaciones se reflejan en boleteria en ~8 segundos.
- Si se cancela un viaje en ventas, desaparece de boleteria automaticamente.
- Compra multiple de 1 a 10 pasajeros.
- Menor enlazado con adulto responsable de la misma compra.
- Venta por vendedor con croquis visual y seleccion de asientos.
- Codigo de viaje unico VJ-AAAAMMDD-NNN en todo el sistema.
- 10 horarios estandar: 06:00, 07:30, 09:00, 10:30, 12:00, 13:30, 15:00, 16:30, 18:00, 20:00
- Selector visual de horarios en CrearViaje: verde=libre, rojo=ocupado, plomo=pasado
- Regla de conflicto de bus: 2:30h minima entre viajes del mismo bus.
- QR de boleto con codigo_viaje incluido.
- Horas AM/PM en todo el sistema.
- Consulta autoridad por codigo_viaje (solo ese metodo).
- Consulta pasajeros por CI.
- Impresion/guardar PDF de boleto y manifiesto.
- Rastreo por codigo boleto, viaje, placa o IMEI GPS.
- Mapa GPS con marcadores SVG profesionales (hexagonos verde/rojo/gris, bus estilizado).
- Mapa en rastrear visible automaticamente sin hacer click.
- ETA durante signal loss calculado con velocidad anterior.
- Croquis con colores: adulto (indigo), adulto mayor (amarillo), adulto con menor (rosa), menor (rojo).
- Simulador de pago: efectivo, tarjeta, QR bancario.
- Control abordaje por QR, huella o validacion dual.
- GPS monitoreo en tiempo real con simulacion:
  - 8 rutas con waypoints reales Cochabamba -> destinos (La Paz, Santa Cruz, Oruro, Potosi, Sucre, Tarija, Trinidad, Cobija)
  - Bus se mueve por la ruta en polling cada 2 segundos.
  - Marcadores SVG unificados en todos los mapas (rastrear, monitoreo, viajes activos modal).
  - Perdida de seal GPS: 12.5% probabilidad, duracion 5-15 segundos, linea punteada en mapa.
  - Auto-completacion: viaje se marca completado con fecha_llegada_real.
  - ETA recalculado en tiempo real basado en progreso y signal loss.
- Dashboard, reportes y auditoria.
- 20 buses con placas unicas.
- 16 rutas Cochabamba ida/vuelta a departamentos de Bolivia.
- 10 viajes demo por dia en venta (estado 'en_venta').
- Estado "programado" removido del sistema. Todos los viajes nuevos nacen como 'en_venta'.

Pendiente recomendado:

- Capturas finales para la defensa.
- Ampliar Swagger con ejemplos completos.
- Probar en dos navegadores los eventos Reverb en tiempo real.
- Ajustar textos finales de marca/logo segun el nombre oficial del bus o empresa.
