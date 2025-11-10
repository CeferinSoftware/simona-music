#!/bin/bash
# Script para habilitar streamers en producción Simona Music
# Ejecutar desde el directorio raíz del proyecto

set -e  # Exit on error

echo "=========================================="
echo "Habilitar Streamers - Simona Music"
echo "=========================================="
echo ""

# Verificar si estamos en el contenedor correcto
if [ ! -f "/var/azuracast/www/composer.json" ]; then
    echo "❌ Error: Este script debe ejecutarse DENTRO del contenedor web de AzuraCast"
    echo "Ejecuta primero: docker exec -it azuracast bash"
    exit 1
fi

echo "✅ Dentro del contenedor AzuraCast"
echo ""

# Pedir confirmación
read -p "¿Estás seguro de habilitar streamers en PRODUCCIÓN? (yes/no): " CONFIRM
if [ "$CONFIRM" != "yes" ]; then
    echo "❌ Operación cancelada"
    exit 0
fi

# Pedir contraseña para el streamer
echo ""
read -s -p "Ingresa una contraseña segura para el streamer dj_terraza: " DJ_PASSWORD
echo ""
read -s -p "Confirma la contraseña: " DJ_PASSWORD_CONFIRM
echo ""

if [ "$DJ_PASSWORD" != "$DJ_PASSWORD_CONFIRM" ]; then
    echo "❌ Error: Las contraseñas no coinciden"
    exit 1
fi

if [ ${#DJ_PASSWORD} -lt 8 ]; then
    echo "❌ Error: La contraseña debe tener al menos 8 caracteres"
    exit 1
fi

echo ""
echo "🔧 Ejecutando SQL para habilitar streamers..."

# Reemplazar PASSWORD_SEGURO_AQUI con la contraseña real
sed "s/PASSWORD_SEGURO_AQUI/$DJ_PASSWORD/g" enable-streamers-production.sql > /tmp/enable-streamers-temp.sql

# Ejecutar SQL en MariaDB
mysql -h mariadb -u azuracast -pazuracast azuracast < /tmp/enable-streamers-temp.sql

# Limpiar archivo temporal
rm /tmp/enable-streamers-temp.sql

echo ""
echo "=========================================="
echo "✅ Streamers habilitados correctamente"
echo "=========================================="
echo ""
echo "📋 Credenciales del streamer:"
echo "   Username: dj_terraza"
echo "   Password: [la que ingresaste]"
echo "   Display Name: DJ Terraza - Simona Music"
echo ""
echo "🌐 URLs de acceso:"
echo "   WebDJ Browser: https://simonamusic.net/public/simona/dj"
echo "   WebSocket: wss://simonamusic.net:8000/live"
echo ""
echo "🎛️ Configuración para software profesional (Traktor/Serato/Mixxx):"
echo "   Host: simonamusic.net"
echo "   Port: 8000"
echo "   Mount: /live"
echo "   Username: source (o dj_terraza)"
echo "   Password: [la que ingresaste]"
echo "   Protocol: Icecast (Source)"
echo ""
echo "📖 Consulta la guía DJ-PROFESSIONAL-GUIDE.md para instrucciones detalladas"
echo ""
