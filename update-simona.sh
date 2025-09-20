#!/bin/bash

# Script de actualización automática para Simona Music
# Funciona como el botón "Actualizar" de AzuraCast oficial

set -e

echo "🚀 Simona Music - Actualización automática"
echo "=========================================="

# Verificar que estamos en el directorio correcto
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ Error: No se encuentra docker-compose.yml"
    echo "   Ejecuta este script desde /root/simona-music"
    exit 1
fi

# Configurar proyecto fijo
echo "📋 Configurando proyecto Docker..."
echo "COMPOSE_PROJECT_NAME=simona-music" > .env.local

# Backup de seguridad antes de actualizar
echo "💾 Creando backup de seguridad..."
BACKUP_DIR="/root/simona-music-backups/$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

docker compose --env-file .env.local exec -T web azuracast_cli azuracast:backup "$BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).tar.gz" || echo "⚠️  Backup falló, continuando..."

# Descargar última versión del código
echo "📥 Descargando última versión..."
git fetch origin main
git pull origin main

# Copiar configuración de producción
cp -f docker-compose.production.yml docker-compose.yml

# Descargar nueva imagen
echo "🐳 Descargando nueva imagen de Simona Music..."
docker compose --env-file .env.local pull

# Parar servicios
echo "⏹️  Parando servicios..."
docker compose --env-file .env.local down

# Iniciar con nueva imagen
echo "▶️  Iniciando con nueva imagen..."
docker compose --env-file .env.local up -d

# Esperar que esté listo
echo "⏳ Esperando que los servicios estén listos..."
sleep 10

# Limpiar cache
echo "🧹 Limpiando cache..."
docker compose --env-file .env.local exec -T web azuracast_cli cache:clear

# Verificar estado
echo "✅ Verificando estado de los servicios..."
docker compose --env-file .env.local ps

echo ""
echo "🎉 ¡Actualización completada!"
echo "🌐 Simona Music disponible en: https://simonamusic.net"
echo "📊 Panel admin: https://simonamusic.net/admin"
echo ""
echo "💡 Para ver logs: docker compose --env-file .env.local logs -f"
echo "💡 Para rollback: docker compose --env-file .env.local down && docker compose --env-file .env.local up -d"
