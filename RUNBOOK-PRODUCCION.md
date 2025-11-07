## Simona Music - Runbook de Producción

### Objetivo
Guía única para desplegar, actualizar y diagnosticar Simona Music (fork de AzuraCast) en producción.

### Arquitectura (Docker Compose)
- **web**: imagen `ghcr.io/ceferinsoftware/simona-music` (con `build` local de respaldo).
- **mariadb**: `mariadb:11.4` (requerido por Doctrine/Migraciones).
- **redis**: `redis:alpine`.
- Volúmenes: `db_data`, `station_data`, `backups`, `www_tmp`, `sftpgo_data`, `geolite_install`, `shoutcast2_install`.

### Principios clave
- No montar el repo completo sobre `/var/azuracast/www` en producción (rompe `vendor/`).
- Usar MariaDB 11.4 (la 10.7 rompe consultas de detección de collation).
- Forzar plataforma MariaDB en Doctrine.
- Desactivar SSL del cliente MariaDB en tareas internas de backup/restore.
- Publicar puertos con binding IPv4 explícito `0.0.0.0:80:80`.

### Ficheros relevantes (en este repo)
- `docker-compose.production.yml`
  - MariaDB 11.4
  - `MYSQL_SSL_MODE=DISABLED` en `web.environment`
  - Montajes puntuales de fixes:
    - `backend/config/services.php` (fijar serverVersion a MariaDB)
    - `backend/src/Console/Command/AbstractDatabaseCommand.php` (desactivar SSL en CLI)
  - Binding IPv4 en puertos 80/443/2022
  - `build:` para compilar imagen local si el pull está denegado
- `azuracast.production.env` → copiar a `azuracast.env` en servidor
- Fixes de código:
  - `backend/config/services.php`: `serverVersion = mariadb-11.4.0` para Docker
  - `AbstractDatabaseCommand.php`: añade `--ssl-mode=DISABLED` y `--ssl=0` a mariadb/mariadb-dump
- `AGENTE-IA-HANDOFF.md` (guía para agentes IA: flujo, comandos críticos, branding y problemas conocidos)

### Flujo de trabajo (dev → prod)
1) Editar en local (frontend/backend/translations).
2) Mantener `docker-compose.production.yml` como fuente de verdad.
3) Commit y push a `main`.
4) VPS (pull + recreate contenedores) y ejecutar setup/migraciones si aplica.

### Despliegue inicial en VPS
```bash
# Requisitos
apt update && apt install -y ca-certificates curl git ufw
curl -fsSL https://get.docker.com -o get-docker.sh && sh get-docker.sh
systemctl enable --now docker

# Código
cd /root
git clone https://github.com/ceferinsoftware/simona-music.git simona-music || true
cd /root/simona-music
cp -f docker-compose.production.yml docker-compose.yml
cp -f azuracast.production.env azuracast.env

# Firewall
ufw --force enable
ufw allow 22/tcp && ufw allow 80/tcp && ufw allow 443/tcp && ufw allow 2022/tcp && ufw allow 8000:8500/tcp

# Arranque limpio (primera vez)
docker compose down || true
docker volume rm simona-music_db_data || true
docker compose up -d --build
sleep 25

# Setup (producción)
docker compose exec -T web azuracast_cli azuracast:setup --init
```

### HTTPS/SSL (Let's Encrypt)

Requisitos previos:
- DNS `A` para `simonamusic.net` y `www.simonamusic.net` apuntando a la IP del VPS
- Puertos abiertos en el servidor: `80/tcp` y `443/tcp`
- En `docker-compose.yml` del servicio `web`:
  - `LETSENCRYPT_HOST: simonamusic.net,www.simonamusic.net`
  - `VIRTUAL_HOST: simonamusic.net,www.simonamusic.net`

Pasos:
```bash
cd /root/simona-music

# 1) Forzar URL base en https
sed -i 's|^INIT_BASE_URL=.*|INIT_BASE_URL=https://simonamusic.net|' azuracast.env
docker compose up -d

# 2) (si da error por redirección) desactivar temporalmente el forzado HTTPS
docker compose exec -T web azuracast_cli azuracast:settings:set always_use_ssl false
docker compose restart web

# 3) Emitir certificados para todos los hosts definidos en LETSENCRYPT_HOST
docker compose exec -T web sh -lc 'echo $LETSENCRYPT_HOST'  # debería mostrar ambos dominios
docker compose exec -T web azuracast_cli azuracast:acme:get-certificate

# 4) Re-activar el forzado HTTPS y limpiar caché
docker compose exec -T web azuracast_cli azuracast:settings:set always_use_ssl true
docker compose exec -T web azuracast_cli cache:clear
docker compose restart web

# 5) Verificar logs de ACME/certificados
docker compose logs --tail=200 web | grep -i -E "acme|letsencrypt|cert|tls"
```

Notas:
- Si aparece "connection refused" en el desafío ACME HTTP-01, asegúrate de que `always_use_ssl` esté en `false` mientras se emite el certificado y que el puerto 80 esté abierto.
- La renovación es automática una vez emitido el certificado.

## 🔄 Actualizaciones en Producción

### ✅ METODOLOGÍA DEFINITIVA (FUNCIONA AL 100%)

**FLUJO PROBADO**: Cambios locales → GitHub → VPS build con Dockerfile corregido.

#### 1. Desarrollo Local → GitHub

```bash
# En tu PC local
git add .
git commit -m "descripción de cambios"
git push origin main
```

#### 2. Actualización en VPS (METODOLOGÍA DEFINITIVA)

```bash
cd /root/simona-music
echo "COMPOSE_PROJECT_NAME=simona-music" > .env.local
git pull origin main
cp -f docker-compose.production.yml docker-compose.yml

# Rebuild completo (CRÍTICO: usar --no-cache para forzar recompilación)
docker compose --env-file .env.local down
docker builder prune -af
docker compose --env-file .env.local build --no-cache web
docker compose --env-file .env.local up -d

# Limpiar cache
docker compose --env-file .env.local exec -T web azuracast_cli cache:clear
```

### 🔧 Configuración Crítica del Dockerfile

**IMPORTANTE**: El Dockerfile debe usar la etapa `development` como base para `final`:

```dockerfile
FROM development AS final

# Cambiar a modo producción
ENV APPLICATION_ENV="production" \
    PROFILING_EXTENSION_ENABLED=0 \
    ENABLE_WEB_UPDATER="true"

# Instalar dependencias PHP de producción
RUN composer install --no-dev --no-ansi --no-autoloader --no-interaction \
    && composer dump-autoload --optimize --classmap-authoritative \
    && composer clear-cache

# Compilar frontend para producción
RUN npm run build \
    && npm cache clean --force
```

### 🎯 Por qué esta metodología funciona

✅ **Dockerfile compila frontend** - Los assets se generan durante la construcción de la imagen  
✅ **Sin bind-mounts problemáticos** - No hay conflictos entre archivos locales y del contenedor  
✅ **Volúmenes persistentes** - No se pierden datos con `COMPOSE_PROJECT_NAME` fijo  
✅ **Frontend incluido en imagen** - Los cambios de branding se compilan en la imagen final  
✅ **Cache limpio** - `--no-cache` y `docker builder prune -af` aseguran rebuild completo  

### 🚨 Comandos Críticos que NO deben faltar

```bash
# SIEMPRE incluir estos comandos:
docker builder prune -af                    # Limpiar cache de Docker
docker compose build --no-cache web         # Rebuild sin cache
docker compose exec -T web azuracast_cli cache:clear  # Limpiar cache de app
```

### 📋 Verificación Post-Actualización

```bash
# Verificar que los cambios se aplicaron
docker compose --env-file .env.local exec -T web bash -lc "find /var/azuracast/www/web/static -name '*.js' -exec grep -l 'Simona Music' {} \;"

# Verificar servicios
docker compose --env-file .env.local ps

# Verificar logs si hay problemas
docker compose --env-file .env.local logs -f web
```

### Diagnóstico rápido
```bash
# Estado y logs
docker compose ps
docker compose logs --tail=200 web

# Puertos en host
ss -lntp | egrep ':80 |:443 |:2022 '
curl -I http://127.0.0.1
curl -I http://SERVIDOR_IP

# Dentro del contenedor web
docker compose exec -T web bash -lc "ss -lntp | egrep ':80|:443' && curl -I http://127.0.0.1 || true"
```

### Problemas conocidos y soluciones aplicadas
- Error: `1.: command not found` al copiar comandos
  - Causa: pegar numeración ("1.") de documentación.
  - Solución: pegar solo los comandos.

- Error: `vendor/autoload.php not found` / CLI rompe
  - Causa: montar `.:/var/azuracast/www` tapa `vendor/` de la imagen.
  - Solución: quitar el bind-mount del repo completo. Montar solo archivos necesarios o construir imagen propia.

- Error migraciones: `Unknown column 'ccsa.FULL_COLLATION_NAME'` (SQLSTATE[42S22])
  - Causa: MariaDB 10.7 y/o autodetección de plataforma como MySQL.
  - Soluciones:
    - Subir a MariaDB `11.4` en compose.
    - Fijar `serverVersion = mariadb-11.4.0` en `backend/config/services.php` cuando se ejecuta en Docker.

- Error CLI backup/restore: `ERROR 2026 (HY000): TLS/SSL is required, but the server does not support it`
  - Solución: `MYSQL_SSL_MODE=DISABLED` en `web.environment` + añadir `--ssl-mode=DISABLED`/`--ssl=0` en comandos CLI.

- Imagen privada denegada (`manifest: denied`)
  - Solución: `build:` local en compose como fallback.

- IP accede con `ERR_CONNECTION_REFUSED`
  - Causa: bind en IPv6 o puertos no publicados.
  - Solución: publicar puertos con `0.0.0.0:80:80`, revisar UFW y `ss -lntp`.

### Comandos útiles de mantenimiento
```bash
# Reiniciar servicio web
docker compose restart web

# Bajar/subir todo
docker compose down && docker compose up -d

# Reset DB (destructivo: borra datos)
docker compose down && docker volume rm simona-music_db_data && docker compose up -d

# Migraciones manuales
docker compose exec -T web azuracast_cli migrations:sync-metadata-storage
docker compose exec -T web azuracast_cli migrations:migrate --no-interaction --allow-no-migration
```

### Flujo recomendado para personalizaciones
- Opción A (recomendada): crear imagen "overlay" FROM oficial y copiar solo cambios.
- Opción B: usar la imagen oficial y montar únicamente carpetas seguras (p. ej. `translations/`).
- Evitar montar `backend/` completo en producción.

### Checklist de entrega/rollback
1) `git push` en local
2) `git pull` en VPS
3) `cp docker-compose.production.yml → docker-compose.yml`
4) `docker compose up -d --build`
5) Verificar `docker compose ps` y `curl -I http://IP`
6) Si falla: revisar logs y secciones de "Problemas conocidos" arriba

### Credenciales iniciales
- Admin inicial configurable en `azuracast.env` (`INIT_ADMIN_EMAIL`, `INIT_ADMIN_PASSWORD`).

### Contacto/Notas
- Zona horaria: `TZ=Europe/Madrid`.
- Estaciones: puertos `8000-8500` expuestos.


