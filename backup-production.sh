#!/bin/bash

# ========================================
# SIMONA MUSIC - SCRIPT DE BACKUP
# ========================================

echo "💾 Creando backup de Simona Music..."

# Colores
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Variables
SERVER_IP="155.138.174.57"
SERVER_USER="root"
REMOTE_PATH="/root/simona-music"
BACKUP_DIR="/root/backups"
DATE=$(date +%Y%m%d_%H%M%S)

echo -e "${BLUE}📡 Conectando al servidor para backup...${NC}"

ssh -o StrictHostKeyChecking=no $SERVER_USER@$SERVER_IP "
# Crear directorio de backups
mkdir -p $BACKUP_DIR

cd $REMOTE_PATH

echo -e '${YELLOW}🗄️ Creando backup de base de datos...${NC}'
docker compose exec -T mariadb mysqldump -u root -pmyself15867D_ azuracast > $BACKUP_DIR/db_backup_$DATE.sql

echo -e '${YELLOW}📁 Creando backup de archivos de estaciones...${NC}'
docker compose exec -T web tar -czf /var/azuracast/backups/stations_backup_$DATE.tar.gz /var/azuracast/stations/

echo -e '${YELLOW}⚙️ Creando backup de configuración...${NC}'
cp azuracast.env $BACKUP_DIR/azuracast_env_$DATE.backup
cp docker-compose.yml $BACKUP_DIR/docker-compose_$DATE.backup

echo -e '${YELLOW}🧹 Limpiando backups antiguos (más de 7 días)...${NC}'
find $BACKUP_DIR -name '*.sql' -mtime +7 -delete
find $BACKUP_DIR -name '*.tar.gz' -mtime +7 -delete
find $BACKUP_DIR -name '*.backup' -mtime +7 -delete

echo -e '${BLUE}📋 Backups disponibles:${NC}'
ls -la $BACKUP_DIR/

echo -e '${GREEN}✅ Backup completado!${NC}'
echo -e '${BLUE}📁 Backups guardados en: $BACKUP_DIR${NC}'
"
