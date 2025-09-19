#!/bin/bash

echo "🎵 CONFIGURACIÓN FINAL DE SIMONA MUSIC"
echo "====================================="

# Ir al directorio correcto
cd /var/azuracast

echo "📁 Directorio actual:"
pwd

echo ""
echo "🔧 Configurando archivo docker-compose correcto..."
cp docker-compose.production.yml docker-compose.yml

echo ""
echo "⚙️ Verificando archivo de configuración..."
ls -la docker-compose.yml

echo ""
echo "🐳 Iniciando servicios con configuración de producción..."
docker compose up -d

echo ""
echo "⏳ Esperando 60 segundos para que se inicien todos los servicios..."
sleep 60

echo ""
echo "🔍 Verificando estado de contenedores:"
docker compose ps

echo ""
echo "🛠️ Ejecutando configuración inicial de base de datos..."
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
echo "🌐 Probando conexión:"
curl -I http://localhost:80 2>&1 || echo "Verificando..."

echo ""
echo "✅ CONFIGURACIÓN COMPLETADA"
echo "=========================="
echo ""
echo "🎵 Simona Music debería estar disponible en:"
echo "   http://155.138.174.57"
echo ""
echo "👤 Credenciales de admin:"
echo "   Email: admin@simonamusic.net"
echo "   Password: myself15867D_"
echo ""
echo "🔧 Si hay problemas, ejecuta:"
echo "   docker compose logs web"
echo "   docker compose exec web supervisorctl status"
