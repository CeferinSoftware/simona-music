#!/bin/bash

# ========================================
# SIMONA MUSIC - SCRIPT DE ACTUALIZACIÓN
# ========================================

echo "🔄 Actualizando Simona Music en producción..."

# Colores
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Variables
SERVER_IP="155.138.174.57"
SERVER_USER="root"
REMOTE_PATH="/root/simona-music"

echo -e "${BLUE}📡 Conectando al servidor...${NC}"

# Ejecutar comandos remotos
ssh -o StrictHostKeyChecking=no $SERVER_USER@$SERVER_IP "
cd $REMOTE_PATH

echo -e '${YELLOW}📥 Descargando últimos cambios...${NC}'
git pull origin main

echo -e '${YELLOW}📦 Copiando archivos locales si existen...${NC}'
# Aquí se copiarían archivos específicos si es necesario

echo -e '${YELLOW}🔄 Reiniciando servicios...${NC}'
docker compose down
docker compose up -d --build

echo -e '${YELLOW}⏱️ Esperando reinicio...${NC}'
sleep 20

echo -e '${YELLOW}🌍 Aplicando traducciones...${NC}'
docker compose exec -T web azuracast_cli locale:import

echo -e '${YELLOW}🧹 Limpiando caché...${NC}'
docker compose exec -T web azuracast_cli cache:clear

echo -e '${BLUE}📊 Estado de servicios:${NC}'
docker compose ps

echo -e '${GREEN}✅ ¡Actualización completada!${NC}'
echo -e '${BLUE}🌐 Simona Music actualizado en: https://simonamusic.net${NC}'
"
