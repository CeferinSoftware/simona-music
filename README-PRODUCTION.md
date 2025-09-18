# 🎵 Simona Music - Guía de Producción

## 📋 Información del Servidor

- **IP:** 155.138.174.57
- **Dominio:** https://simonamusic.net
- **Usuario Admin:** admin@simonamusic.net
- **Contraseña Admin:** myself15867D_

## 🚀 Comandos Principales

### Deployment Inicial
```bash
# Hacer el script ejecutable
chmod +x deploy-to-production.sh
# Ejecutar deployment
./deploy-to-production.sh
```

### Actualizar Producción
```bash
# Después de hacer cambios locales
git add .
git commit -m "feat: nuevas mejoras"
git push origin main

# Actualizar en producción
chmod +x update-production.sh
./update-production.sh
```

### Crear Backup
```bash
chmod +x backup-production.sh
./backup-production.sh
```

## 🔧 Comandos Directos en Servidor

### Conectar al servidor
```bash
ssh root@155.138.174.57
cd /root/simona-music
```

### Ver estado de servicios
```bash
docker compose ps
docker compose logs web
```

### Comandos de AzuraCast
```bash
# Ver logs
docker compose logs -f web

# Acceder al contenedor
docker compose exec web bash

# Comandos útiles
docker compose exec web azuracast_cli cache:clear
docker compose exec web azuracast_cli locale:import
docker compose exec web azuracast_cli azuracast:setup --init
```

## 🌐 Configuración DNS (Namecheap)

**Registros necesarios:**
```
Tipo: A Record
Host: @
Value: 155.138.174.57

Tipo: A Record  
Host: www
Value: 155.138.174.57

Tipo: CNAME Record
Host: *
Value: simonamusic.net
```

## 🔐 Datos de Acceso

### Panel Admin
- **URL:** https://simonamusic.net/admin
- **Email:** admin@simonamusic.net
- **Password:** myself15867D_

### Base de Datos
- **Host:** mariadb (dentro del contenedor)
- **User:** azuracast
- **Password:** myself15867D_
- **Database:** azuracast

## 📁 Estructura de Archivos

```
/root/simona-music/
├── docker-compose.yml          # Configuración principal
├── azuracast.env              # Variables de entorno
├── frontend/                  # Código frontend personalizado
├── backend/                   # Código backend personalizado
├── translations/              # Traducciones personalizadas
└── /root/backups/            # Backups automáticos
```

## 🆘 Solución de Problemas

### Si el sitio no carga:
```bash
# Reiniciar todos los servicios
docker compose down
docker compose up -d

# Ver logs de errores
docker compose logs web
```

### Si hay problemas con SSL:
```bash
# Verificar certificados
docker compose exec web certbot certificates

# Renovar certificados
docker compose exec web certbot renew
```

### Si la base de datos falla:
```bash
# Restaurar último backup
docker compose exec -T mariadb mysql -u root -pmyself15867D_ azuracast < /root/backups/db_backup_FECHA.sql
```

## 📊 Monitoreo

### URLs importantes:
- **Sitio principal:** https://simonamusic.net
- **Panel admin:** https://simonamusic.net/admin
- **API:** https://simonamusic.net/api
- **Público:** https://simonamusic.net/public

### Verificar estado:
```bash
# Estado de contenedores
docker compose ps

# Uso de recursos
docker stats

# Logs en tiempo real
docker compose logs -f
```

## 🔄 Flujo de Desarrollo

1. **Hacer cambios localmente**
2. **Probar en desarrollo:** `docker compose up -d`
3. **Commitear:** `git add . && git commit -m "descripción"`
4. **Subir:** `git push origin main`
5. **Actualizar producción:** `./update-production.sh`

## 🎉 ¡Listo!

Tu instalación de **Simona Music** está funcionando en producción con:
- ✅ SSL automático
- ✅ Backups programados
- ✅ Monitoreo de servicios
- ✅ Scripts de actualización automática
