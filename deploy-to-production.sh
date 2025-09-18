#!/bin/bash

# ========================================
# SIMONA MUSIC - SCRIPT DE DEPLOYMENT
# ========================================

echo "🚀 Iniciando deployment de Simona Music..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Variables
SERVER_IP="155.138.174.57"
SERVER_USER="root"
REMOTE_PATH="/root/simona-music"
GITHUB_REPO="https://github.com/ceferinsoftware/simona-music.git"

echo -e "${BLUE}📡 Conectando al servidor ${SERVER_IP}...${NC}"

# Función para ejecutar comandos remotos
remote_exec() {
    ssh -o StrictHostKeyChecking=no $SERVER_USER@$SERVER_IP "$1"
}

# Función para copiar archivos
copy_files() {
    scp -o StrictHostKeyChecking=no -r "$1" $SERVER_USER@$SERVER_IP:"$2"
}

echo -e "${YELLOW}🔧 Preparando servidor...${NC}"

# Crear directorio y clonar repo
remote_exec "
if [ ! -d '$REMOTE_PATH' ]; then
    echo '📁 Creando directorio...'
    mkdir -p $REMOTE_PATH
    cd $REMOTE_PATH
    echo '📥 Clonando repositorio...'
    git clone $GITHUB_REPO .
else
    echo '🔄 Actualizando código...'
    cd $REMOTE_PATH
    git pull origin main
fi
"

echo -e "${YELLOW}📦 Copiando archivos de configuración...${NC}"

# Copiar archivos de configuración
copy_files "docker-compose.production.yml" "$REMOTE_PATH/docker-compose.yml"
copy_files "azuracast.production.env" "$REMOTE_PATH/azuracast.env"

echo -e "${YELLOW}🐳 Instalando Docker si no existe...${NC}"

remote_exec "
if ! command -v docker &> /dev/null; then
    echo '🔽 Instalando Docker...'
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
    systemctl enable docker
    systemctl start docker
else
    echo '✅ Docker ya está instalado'
fi
"

echo -e "${YELLOW}🔥 Configurando firewall...${NC}"

remote_exec "
ufw --force enable
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw allow 2022/tcp  # SFTP
ufw allow 8000:8500/tcp  # Radio streams
echo '🛡️ Firewall configurado'
"

echo -e "${YELLOW}🚀 Desplegando Simona Music...${NC}"

remote_exec "
cd $REMOTE_PATH
echo '⬇️ Descargando imágenes...'
docker compose pull
echo '🔨 Construyendo servicios...'
docker compose up -d
echo '⏱️ Esperando que los servicios se inicien...'
sleep 30
echo '🎵 Configurando Simona Music...'
docker compose exec -T web azuracast_cli azuracast:setup --init
echo '🌍 Importando traducciones...'
docker compose exec -T web azuracast_cli locale:import
echo '🧹 Limpiando caché...'
docker compose exec -T web azuracast_cli cache:clear
"

echo -e "${GREEN}✅ ¡Deployment completado!${NC}"
echo -e "${BLUE}🌐 Tu instalación de Simona Music estará disponible en:${NC}"
echo -e "${YELLOW}   https://simonamusic.net${NC}"
echo -e "${YELLOW}   Credenciales: admin@simonamusic.net / myself15867D_${NC}"

echo -e "${BLUE}📊 Verificando estado de los servicios...${NC}"
remote_exec "cd $REMOTE_PATH && docker compose ps"

echo -e "${GREEN}🎉 ¡Simona Music está funcionando en producción!${NC}"
