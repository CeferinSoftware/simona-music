#!/bin/bash

# ========================================
# SIMONA MUSIC - DEPLOYMENT EN AZURACAST EXISTENTE
# ========================================

echo "🎵 Desplegando Simona Music en servidor con AzuraCast preinstalado..."

# Variables
SERVER_IP="155.138.174.57"
SERVER_USER="root"
SERVER_PASS="?5BgP2Jfm)sycWo9"
AZURACAST_DIR="/var/azuracast"

# Colores
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}📡 Conectando al servidor ${SERVER_IP}...${NC}"

# Usar sshpass para autenticación automática
echo -e "${YELLOW}🔍 Verificando AzuraCast existente...${NC}"

sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no $SERVER_USER@$SERVER_IP "
# Verificar si AzuraCast está instalado
if [ -d '$AZURACAST_DIR' ]; then
    echo -e '${GREEN}✅ AzuraCast encontrado en $AZURACAST_DIR${NC}'
    cd $AZURACAST_DIR
    
    echo -e '${YELLOW}📋 Estado actual de AzuraCast:${NC}'
    docker compose ps
    
    echo -e '${YELLOW}🛑 Deteniendo servicios temporalmente...${NC}'
    docker compose down
    
    echo -e '${YELLOW}📁 Creando backup de configuración actual...${NC}'
    mkdir -p /root/backups
    cp azuracast.env /root/backups/azuracast.env.backup.\$(date +%Y%m%d_%H%M%S)
    
    echo -e '${YELLOW}⚙️ Configurando para Simona Music...${NC}'
    
    # Actualizar configuración
    cat > azuracast.env << 'EOF'
# ===========================================
# SIMONA MUSIC - CONFIGURACIÓN DE PRODUCCIÓN
# ===========================================

APPLICATION_ENV=production
LOG_LEVEL=notice

# --- CONFIGURACIÓN INICIAL ---
INIT_ADMIN_EMAIL=admin@simonamusic.net
INIT_ADMIN_PASSWORD=myself15867D_
INIT_BASE_URL=http://155.138.174.57
INIT_INSTANCE_NAME=Simona Music

# --- TIMEZONE ---
TZ=Europe/Madrid

# --- CONFIGURACIONES ADICIONALES ---
ENABLE_REDIS=true
ENABLE_ADVANCED_FEATURES=true
PREFER_RELEASE_BUILDS=true
CHECK_FOR_UPDATES=true

# --- PUERTOS ---
AZURACAST_HTTP_PORT=80
AZURACAST_HTTPS_PORT=443
AZURACAST_SFTP_PORT=2022
EOF

    echo -e '${YELLOW}🚀 Reiniciando AzuraCast con nueva configuración...${NC}'
    docker compose up -d
    
    echo -e '${YELLOW}⏱️ Esperando que los servicios se inicien...${NC}'
    sleep 30
    
    echo -e '${YELLOW}🔧 Configurando Simona Music...${NC}'
    docker compose exec -T web azuracast_cli azuracast:setup --load-fixtures
    
else
    echo -e '${RED}❌ AzuraCast no encontrado. Instalando desde cero...${NC}'
    
    # Clonar AzuraCast
    cd /root
    git clone https://github.com/AzuraCast/AzuraCast.git azuracast
    cd azuracast
    
    # Configurar
    cp docker-compose.sample.yml docker-compose.yml
    cp azuracast.env.sample azuracast.env
    
    # Configurar para Simona Music
    cat > azuracast.env << 'EOF'
# Configuración Simona Music
APPLICATION_ENV=production
INIT_ADMIN_EMAIL=admin@simonamusic.net
INIT_ADMIN_PASSWORD=myself15867D_
INIT_BASE_URL=http://155.138.174.57
INIT_INSTANCE_NAME=Simona Music
TZ=Europe/Madrid
EOF

    # Instalar
    docker compose up -d
    sleep 30
    docker compose exec -T web azuracast_cli azuracast:setup --init
fi

echo -e '${BLUE}📊 Estado final de servicios:${NC}'
docker compose ps

echo -e '${GREEN}✅ ¡Deployment completado!${NC}'
echo -e '${BLUE}🌐 Simona Music disponible en: http://155.138.174.57${NC}'
echo -e '${YELLOW}👤 Admin: admin@simonamusic.net / myself15867D_${NC}'
"
