#!/bin/bash

echo "🔧 ARREGLANDO PROBLEMA DE VENDOR/AUTOLOAD.PHP"
echo "=============================================="

# Ir al directorio de AzuraCast
cd /var/azuracast

echo "📁 Verificando directorio actual:"
pwd
ls -la

echo ""
echo "🔍 Verificando estado de los contenedores:"
docker compose ps

echo ""
echo "🛠️ Instalando dependencias de Composer dentro del contenedor web:"
docker compose exec -T web composer install --no-dev --optimize-autoloader

echo ""
echo "🔍 Verificando que vendor/autoload.php existe:"
docker compose exec -T web ls -la vendor/autoload.php

echo ""
echo "🔄 Reiniciando servicios:"
docker compose restart web

echo ""
echo "⏳ Esperando 30 segundos para que los servicios se inicien..."
sleep 30

echo ""
echo "🔍 Verificando estado final:"
docker compose ps

echo ""
echo "🌐 Probando conexión web:"
curl -I http://localhost || echo "❌ Error de conexión"

echo ""
echo "✅ PROCESO COMPLETADO"
echo "Ahora prueba acceder a: http://155.138.174.57"
echo ""
echo "Si aún no funciona, ejecuta:"
echo "docker compose logs web"
