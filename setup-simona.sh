#!/bin/bash
# Script para configurar Simona Music en el servidor

echo "🎵 Configurando Simona Music..."

# Ir al directorio de AzuraCast
cd /var/azuracast || { echo "❌ No se encontró /var/azuracast"; exit 1; }

echo "📍 Ubicación actual: $(pwd)"
echo "📁 Contenido del directorio:"
ls -la

echo "📊 Estado actual de servicios:"
docker compose ps

echo "🛑 Deteniendo servicios..."
docker compose down

echo "⚙️ Creando configuración de Simona Music..."
cat > azuracast.env << 'EOF'
APPLICATION_ENV=production
LOG_LEVEL=notice
INIT_ADMIN_EMAIL=admin@simonamusic.net
INIT_ADMIN_PASSWORD=myself15867D_
INIT_BASE_URL=http://155.138.174.57
INIT_INSTANCE_NAME=Simona Music
TZ=Europe/Madrid
AZURACAST_HTTP_PORT=80
AZURACAST_HTTPS_PORT=443
AZURACAST_SFTP_PORT=2022
ENABLE_REDIS=true
ENABLE_ADVANCED_FEATURES=true
PREFER_RELEASE_BUILDS=true
CHECK_FOR_UPDATES=true
EOF

echo "✅ Configuración creada:"
cat azuracast.env

echo "🚀 Iniciando servicios..."
docker compose up -d

echo "⏱️ Esperando que los servicios se inicien (30 segundos)..."
sleep 30

echo "🔧 Configurando AzuraCast inicial..."
docker compose exec -T web azuracast_cli azuracast:setup --load-fixtures

echo "📊 Estado final de servicios:"
docker compose ps

echo ""
echo "🎉 ¡SIMONA MUSIC CONFIGURADO!"
echo "🌐 Acceso: http://155.138.174.57"
echo "👤 Usuario: admin@simonamusic.net"
echo "🔑 Contraseña: myself15867D_"
echo ""
