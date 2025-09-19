#!/bin/bash

echo "🎵 REINSTALACIÓN COMPLETA DE SIMONA MUSIC"
echo "========================================="

# Detener y limpiar todo
echo "🧹 Limpiando instalación anterior..."
cd /var/azuracast 2>/dev/null || echo "Directorio no existe"
docker compose down -v 2>/dev/null || echo "No hay contenedores ejecutándose"
docker system prune -af --volumes

# Eliminar directorio anterior
echo "🗑️ Eliminando directorio anterior..."
cd /
rm -rf /var/azuracast

# Clonar nuestro repositorio personalizado
echo "📥 Descargando Simona Music desde GitHub..."
git clone https://github.com/CeferinSoftware/simona-music.git /var/azuracast
cd /var/azuracast

# Configurar archivo de entorno
echo "⚙️ Configurando archivo de entorno..."
cp azuracast.production.env azuracast.env

# Verificar archivo de configuración
echo "🔍 Contenido del archivo de configuración:"
cat azuracast.env

echo ""
echo "🐳 Iniciando contenedores Docker..."
docker compose up -d

echo ""
echo "⏳ Esperando 60 segundos para que se inicien todos los servicios..."
sleep 60

echo ""
echo "🔍 Verificando estado de contenedores:"
docker compose ps

echo ""
echo "📋 Logs del contenedor web:"
docker compose logs --tail=20 web

echo ""
echo "🌐 Probando conexión local:"
curl -I http://localhost:80 2>&1

echo ""
echo "🔍 Verificando puertos:"
netstat -tlnp | grep :80

echo ""
echo "🛠️ Ejecutando configuración inicial de AzuraCast..."
docker compose exec -T web azuracast_cli azuracast:setup --init

echo ""
echo "🔄 Reiniciando servicios web..."
docker compose restart web

echo ""
echo "⏳ Esperando 30 segundos más..."
sleep 30

echo ""
echo "🔍 Estado final:"
docker compose ps

echo ""
echo "🌐 Prueba final de conexión:"
curl -I http://localhost:80 2>&1

echo ""
echo "✅ PROCESO COMPLETADO"
echo "===================="
echo ""
echo "🎵 Simona Music debería estar disponible en:"
echo "   http://155.138.174.57"
echo ""
echo "👤 Credenciales de admin:"
echo "   Email: admin@simonamusic.net"
echo "   Password: myself15867D_"
echo ""
echo "🔧 Si aún hay problemas, ejecuta:"
echo "   docker compose logs web"
