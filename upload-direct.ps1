# SUBIR DIRECTAMENTE NUESTRO CODIGO COMPLETO
Write-Host "Subiendo nuestro codigo completo de Simona Music..." -ForegroundColor Blue

# Crear archivo tar con TODO nuestro código
Write-Host "Creando paquete..." -ForegroundColor Yellow
tar -czf simona-complete.tar.gz . --exclude=node_modules --exclude=.git --exclude=vendor

# Subir al servidor
Write-Host "Subiendo al servidor..." -ForegroundColor Yellow
scp simona-complete.tar.gz root@155.138.174.57:/tmp/

# Descomprimir y configurar en el servidor
Write-Host "Configurando en servidor..." -ForegroundColor Yellow
$setupScript = @"
cd /var
rm -rf azuracast
mkdir azuracast
cd azuracast
tar -xzf /tmp/simona-complete.tar.gz
cp azuracast.production.env azuracast.env
cp azuracast.production.env .env
cp docker-compose.production.yml docker-compose.yml
docker compose up -d
sleep 30
docker compose exec -T web azuracast_cli azuracast:setup --init
echo 'LISTO! http://155.138.174.57'
"@

$setupScript | ssh root@155.138.174.57

# Limpiar
Remove-Item simona-complete.tar.gz -ErrorAction SilentlyContinue

Write-Host "Simona Music listo!" -ForegroundColor Green

