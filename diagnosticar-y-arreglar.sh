#!/bin/bash

echo "🔍 DIAGNÓSTICO COMPLETO DE SIMONA MUSIC"
echo "======================================="

# Ir al directorio de AzuraCast
cd /var/azuracast

echo ""
echo "📁 Directorio actual:"
pwd
ls -la

echo ""
echo "🐳 Estado de contenedores Docker:"
docker compose ps

echo ""
echo "📋 Logs del contenedor web (últimas 30 líneas):"
docker compose logs --tail=30 web

echo ""
echo "🔍 Verificando puertos abiertos:"
netstat -tlnp | grep :80
netstat -tlnp | grep :443

echo ""
echo "🌐 Probando conexión local:"
curl -I http://localhost:80 2>&1 || echo "❌ No responde en puerto 80"

echo ""
echo "🔧 INTENTANDO ARREGLAR..."
echo "========================="

echo "1. Parando todos los servicios..."
docker compose down

echo ""
echo "2. Limpiando volúmenes problemáticos..."
docker system prune -f

echo ""
echo "3. Recreando todo desde cero..."
docker compose up -d

echo ""
echo "4. Esperando 60 segundos para que se inicien los servicios..."
sleep 60

echo ""
echo "5. Verificando estado después del reinicio:"
docker compose ps

echo ""
echo "6. Verificando logs después del reinicio:"
docker compose logs --tail=20 web

echo ""
echo "7. Probando conexión después del arreglo:"
curl -I http://localhost:80 2>&1

echo ""
echo "8. Verificando puertos después del arreglo:"
netstat -tlnp | grep :80

echo ""
echo "✅ DIAGNÓSTICO COMPLETADO"
echo "========================="
echo ""
echo "Si aún no funciona, ejecuta:"
echo "docker compose logs web"
echo ""
echo "Y luego prueba:"
echo "docker compose exec web azuracast_cli azuracast:setup --init"
