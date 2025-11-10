# Fix para visualización de videoclips en página pública

## Problema identificado
El error `FullPlayer: No song in currentNp` ocurre porque el componente intenta acceder a `video_url` antes de que los datos reales de "now playing" estén cargados. El flujo es:

1. **Primera actualización**: Llega con `now_playing` vacío (`sh_id: 0`)
2. **Segunda actualización**: Llega con los datos reales de la canción

## Solución implementada

### Cambios en `frontend/components/Public/FullPlayer.vue`
✅ Validación robusta en `currentSong` computed:
- Verificar que `now_playing` existe
- Verificar que `sh_id > 0` (indica canción real)
- Verificar que `song` y `song.title` existen
- Solo entonces acceder a `video_url`

### Cambios en `frontend/components/Public/FullscreenDisplay.vue`
✅ Validación completa de video_url:
- Verificar que existe `currentSong`
- Verificar que `displayMode === 'videoclips'`
- Verificar que `video_url` no está vacío

✅ Mejoras en conversión de URLs:
- Soporte mejorado para múltiples formatos de YouTube
- Mejor logging para debugging
- Controles habilitados en el embed (`controls=1`)
- Loop funcional con `playlist` parameter para YouTube

✅ Mejores funciones de extracción de IDs:
- Patrones regex más robustos
- Logging detallado de éxito/fallo

## Comandos para actualizar en producción (VPS)

**IMPORTANTE**: Estos comandos NO borran datos ni volúmenes. Solo actualizan el código.

### Paso 1: Conectar al servidor
```bash
ssh root@155.138.174.57
```

### Paso 2: Hacer backup preventivo
```bash
cd /root/simona-music
docker compose exec web azuracast_cli azuracast:backup /var/azuracast/backups/backup_pre_videoclip_fix_$(date +%Y%m%d_%H%M%S).zip
```

### Paso 3: Actualizar código desde GitHub
```bash
cd /root/simona-music
git pull origin main
```

### Paso 4: Actualizar sin perder datos

**OPCIÓN A - RESTART (Recomendado - Más rápido, 30 segundos)**
```bash
docker compose -f docker-compose.production.yml restart web
```

**OPCIÓN B - BUILD COMPLETO (Solo si RESTART no funciona)**
```bash
cd /root/simona-music
echo "COMPOSE_PROJECT_NAME=simona-music" > .env.local
cp -f docker-compose.production.yml docker-compose.yml

# Rebuild sin borrar volúmenes
docker compose --env-file .env.local build --no-cache web
docker compose --env-file .env.local up -d --force-recreate web

# Limpiar cache
docker compose --env-file .env.local exec -T web azuracast_cli cache:clear
```

### Paso 5: Verificar que funciona
```bash
# Ver logs para confirmar que arrancó bien
docker compose logs --tail=100 web

# Verificar servicios
docker compose ps

# Probar acceso
curl -I https://simonamusic.net
```

### Paso 6: Probar la funcionalidad
1. Ve a una canción en el admin y pon un video URL de YouTube
   - Ejemplo: `https://www.youtube.com/watch?v=SaRUVzmf74&list=...`
2. En la estación, verifica que "Public Player Display Mode" esté en "Video Clips (YouTube/Vimeo)"
3. Abre la página pública con `?display` o `?fullscreen`
   - Ejemplo: `https://simonamusic.net/public/laclave_terraza1?display`
4. El video debería verse y escucharse automáticamente

## ¿Qué hace cada comando?

### `git pull origin main`
- Descarga los últimos cambios del código desde GitHub
- NO afecta la base de datos ni volúmenes
- Solo actualiza archivos de código

### `docker compose restart web`
- Reinicia solo el contenedor web
- Preserva todos los volúmenes y datos
- El frontend se recompila automáticamente al arrancar
- **Tiempo**: ~30 segundos

### `docker compose build --no-cache web`
- Reconstruye la imagen del contenedor desde cero
- Útil si hay cambios en dependencias (composer.json, package.json)
- NO borra volúmenes ni datos
- **Tiempo**: ~10-15 minutos

### `docker compose up -d --force-recreate web`
- Levanta el contenedor con la nueva imagen
- `--force-recreate` asegura que use la imagen recién compilada
- `-d` lo ejecuta en background
- NO borra volúmenes (los volúmenes persisten independientemente)

## Comandos que NUNCA debes usar en producción

❌ **NUNCA ejecutar**:
```bash
docker compose down  # Puede perder referencia a volúmenes
docker volume rm simona-music_db_data  # BORRA LA BASE DE DATOS
azuracast:setup --init  # BORRA TODA LA CONFIGURACIÓN
```

## Troubleshooting

### Si el video no se muestra:
1. Verificar en consola del navegador (F12) los logs:
   - `FullscreenDisplay: Valid video URL found:`
   - `FullscreenDisplay: YouTube embed URL:`
2. Verificar que la URL del video es válida
3. Verificar que `displayMode` está en `'videoclips'`

### Si el servicio no arranca:
```bash
# Ver logs detallados
docker compose logs --tail=200 web

# Reintentar
docker compose restart web
```

### Si se perdió acceso al servidor:
1. Entrar a Vultr console
2. Verificar que el snapshot más reciente está disponible
3. NO restaurar a menos que sea absolutamente necesario

## Notas importantes

- ✅ Los volúmenes (`db_data`, `station_data`, etc.) persisten automáticamente
- ✅ El `COMPOSE_PROJECT_NAME=simona-music` asegura que Docker encuentre los volúmenes correctos
- ✅ El frontend se recompila automáticamente al iniciar el contenedor
- ✅ Las migraciones NO son necesarias (solo cambios en frontend)
- ✅ El backup preventivo te permite volver atrás si algo falla

## Resultado esperado

Después de la actualización:
1. ✅ Videos de YouTube/Vimeo se muestran en fullscreen/display
2. ✅ El audio del video se escucha automáticamente
3. ✅ Si no hay video_url, se muestra la visualización de ondas (fallback)
4. ✅ Widget QR siempre visible para que usuarios soliciten canciones
5. ✅ Sin errores en consola del navegador
