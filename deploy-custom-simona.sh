#!/bin/bash

# ============================================
# DEPLOY PERSONALIZADO DE SIMONA MUSIC
# ============================================

echo "🎵 Desplegando Simona Music personalizado..."

SERVER_IP="155.138.174.57"
SERVER_USER="root"

echo "🗑️ Limpiando instalación anterior..."
ssh $SERVER_USER@$SERVER_IP "
# Detener y eliminar instalación anterior
cd /var/azuracast 2>/dev/null && docker compose down 2>/dev/null
cd /var && rm -rf azuracast

# Crear directorio limpio
mkdir -p /var/azuracast
"

echo "📦 Subiendo código personalizado de Simona Music..."

# Crear archivo tar con nuestro código
tar -czf simona-music.tar.gz \
    frontend/ \
    backend/ \
    translations/ \
    docker-compose.production.yml \
    azuracast.production.env \
    README-PRODUCTION.md

# Subir al servidor
scp simona-music.tar.gz $SERVER_USER@$SERVER_IP:/tmp/

echo "🚀 Instalando Simona Music en el servidor..."
ssh $SERVER_USER@$SERVER_IP "
cd /var/azuracast

# Descomprimir nuestro código
tar -xzf /tmp/simona-music.tar.gz

# Clonar AzuraCast base y aplicar nuestras personalizaciones
git clone https://github.com/AzuraCast/AzuraCast.git .
git checkout stable

# Copiar nuestras personalizaciones
cp docker-compose.production.yml docker-compose.yml
cp azuracast.production.env azuracast.env

# Aplicar nuestras personalizaciones de frontend
cp -r frontend/* ./ 2>/dev/null || true
cp -r backend/* ./ 2>/dev/null || true
cp -r translations/* ./ 2>/dev/null || true

echo '✅ Configurando Simona Music...'
docker compose up -d

echo '⏱️ Esperando que se inicien los servicios...'
sleep 30

echo '🔧 Configuración inicial...'
docker compose exec -T web azuracast_cli azuracast:setup --init

echo '🌍 Importando traducciones personalizadas...'
docker compose exec -T web azuracast_cli locale:import

echo '🧹 Limpiando caché...'
docker compose exec -T web azuracast_cli cache:clear

echo '📊 Estado final:'
docker compose ps

rm -f /tmp/simona-music.tar.gz
"

# Limpiar archivo local
rm -f simona-music.tar.gz

echo ""
echo "🎉 ¡SIMONA MUSIC DESPLEGADO!"
echo "🌐 Acceso: http://155.138.174.57"
echo "👤 Admin: admin@simonamusic.net / myself15867D_"
echo ""

