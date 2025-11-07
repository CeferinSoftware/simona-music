## Guía de Handoff para Agente IA – Proyecto Simona Music (fork de AzuraCast)

### Objetivo
Este documento permite a cualquier agente IA continuar el proyecto Simona Music sin romper producción ni perder datos. Define el flujo de trabajo, comandos críticos de actualización, convenciones de edición y los problemas conocidos ya resueltos.

### Stack y contexto
- Base: AzuraCast (PHP + Vue)
- Orquestación: Docker + Docker Compose
- Base de datos: MariaDB (en contenedor)
- Entorno producción: VPS Ubuntu en Vultr
- Dominio/SSL: simonamusic.net (Let’s Encrypt automático dentro del stack)
- Repositorio: este repo local (Simona Music)

### Principios operativos (obligatorios)
1. Responder SIEMPRE en español, claro y conciso.
2. Realizar cambios como “edits” en archivos específicos. No sugerir pasos ambiguos: aplicar directamente en el repo.
3. Nunca montar el repo completo dentro del contenedor en producción (evitar sobrescribir `vendor/`).
4. Mantener `COMPOSE_PROJECT_NAME=simona-music` para conservar volúmenes y datos.
5. Compilar el frontend dentro de la imagen Docker (no construir assets en runtime).
6. Tras cambios, reconstruir la imagen SIN caché y limpiar cachés (Docker y app).
7. Confirmar cambios en assets compilados antes de dar por terminado.

### Flujo de trabajo con el usuario
1. El usuario indica QUÉ cambiar y DÓNDE (archivo/ruta y resultado esperado).
2. El agente aplica los edits en el repo local (PHP/Vue/JSON/etc.).
3. El agente devuelve al usuario:
   - Lista de archivos editados
   - Mensaje de commit sugerido
   - Comandos exactos para PC local (git add/commit/push)
   - Comandos exactos para actualizar en el VPS

### Comandos estándar del usuario
- En su PC (después de los edits):
```
git add .
git commit -m "feat/fix: descripción del cambio"
git push origin main
```

- En el VPS (siempre este bloque completo):
```
cd /root/simona-music
echo "COMPOSE_PROJECT_NAME=simona-music" > .env.local
git pull origin main
cp -f docker-compose.production.yml docker-compose.yml

docker compose --env-file .env.local down
docker builder prune -af
docker compose --env-file .env.local build --no-cache web
docker compose --env-file .env.local up -d

docker compose --env-file .env.local exec -T web azuracast_cli cache:clear
```

### Configuración crítica (ya resuelta y obligatoria)
- `Dockerfile` (etapa final): debe heredar de la etapa `development` para disponer de Node y poder ejecutar `npm run build` durante el build. Ejemplo (resumen):
```
FROM development AS final
ENV APPLICATION_ENV="production" \
    PROFILING_EXTENSION_ENABLED=0 \
    ENABLE_WEB_UPDATER="true"
RUN composer install --no-dev --no-ansi --no-autoloader --no-interaction \
    && composer dump-autoload --optimize --classmap-authoritative \
    && composer clear-cache
RUN npm run build && npm cache clean --force
```

- `docker-compose.production.yml`:
  - Usar `build:` para el servicio `web` (no `image:`) y evitar bind-mounts de todo el repo.
  - Puertos: 80/443/2022 expuestos; rango 8000-8500 para estaciones.
  - Variables para SSL automático (`LETSENCRYPT_HOST`, `LETSENCRYPT_EMAIL`, `VIRTUAL_HOST`).
  - `MYSQL_SSL_MODE=DISABLED` en tareas internas si procede.

### Verificación tras despliegue
- Confirmar que los assets contienen el branding:
```
docker compose --env-file .env.local exec -T web bash -lc "find /var/azuracast/www/web/static -name '*.js' -exec grep -l 'Simona Music' {} \;"
```
- Estado y logs:
```
docker compose --env-file .env.local ps
docker compose --env-file .env.local logs -f web
```

### Branding y enlaces externos (política aplicada)
- Reemplazar “AzuraCast” por “Simona Music” en textos visibles, sin tocar nombres técnicos críticos.
- Eliminar enlaces a documentación externa de AzuraCast (UI no debe exponer docs).
- Se han editado ya múltiples componentes (Admin/Relays, Shoutcast, RSAS, Stereo Tool, SoundExchange, Mounts, Logs, Playlists, Backend/AutoDJ, API Keys) y la página de “Forgot Password” deshabilitada en backend para quitar el botón de docs.
- Actualizar `translations/es_ES.UTF-8/translations.json` coherentemente cuando se cambien textos.

### Problemas conocidos (ya resueltos) y cómo evitarlos
- “Tras reiniciar, vuelve el instalador”: usar siempre `COMPOSE_PROJECT_NAME=simona-music` para fijar volúmenes.
- “No se ven cambios de branding”: reconstruir imagen sin caché, compilar frontend en build, limpiar cachés, evitar `image:` remoto y montar el repo completo dentro del contenedor.
- “npm/vite not found dentro de la imagen”: la etapa final debe heredar de `development` para disponer de Node.

### Checklist antes de cerrar una tarea
1. Edits aplicados en archivos correctos (mantener estilo y sangría existente).
2. Traducciones actualizadas si hay textos nuevos.
3. Mensaje de commit claro y corto.
4. Instrucciones de PC (git) y VPS (compose) incluidas.
5. Comandos de verificación añadidos si aplica.

### Cómo implementar nuevas funcionalidades reutilizando AzuraCast (resumen mínimo viable)
1) Botón “Siguiente canción” por estación:
   - Reutilizar AutoDJ; exponer endpoint/acción para “skip/next” y botón en UI local/DJ global.
   - Feedback por polling al Now Playing.
2) Vista global multi-terrazas (multi-estaciones):
   - Reutilizar endpoints de estado/Now Playing por estación y dibujar una tabla/mosaico.
3) Catálogo + filtros + “Añadir a playlist activa”:
   - Reutilizar biblioteca y endpoints de playlists; exponer filtros visibles y acción de añadir.
4) QR clientes (solicitudes):
   - Reutilizar Requests. Añadir campos opcionales (nombre/avatar) como comentario y mostrar estado por polling.
5) Temas por estación (branding local):
   - Guardar esquema de colores/logo en settings por estación y aplicar via CSS variables.
6) Pantallas (lógico, sin control hardware):
   - Metadatos por estación para definir/activar/asignar contenido (placeholder). UI simple.

### Convenciones de commits y respuestas del agente
- Commit: `feat: ...`, `fix: ...`, `branding: ...`, `docs: ...`
- La respuesta al usuario SIEMPRE debe incluir:
  - Resumen corto del cambio
  - Archivos editados
  - Mensaje de commit
  - Bloques de comandos para PC y para VPS

### Seguridad y configuración
- Mantener credenciales en los `.env` del stack; no imprimir secretos en logs/respuestas.
- Validar que puertos/hostnames coinciden con `simonamusic.net`.

### Dónde encontrar lo importante en este repo
- `docker-compose.production.yml` (stack producción)
- `Dockerfile` (build imagen web con assets)
- `frontend/...` (Vue UI)
- `backend/...` (PHP backend y templates)
- `translations/es_ES.UTF-8/translations.json` (strings)
- `RUNBOOK-PRODUCCION.md` (procedimientos operativos)
- Este archivo: `AGENTE-IA-HANDOFF.md` (normas para el agente)

---
Con este flujo, otro agente IA puede continuar el proyecto sin romper branding ni despliegues, manteniendo datos y entregando cambios de forma fiable.



