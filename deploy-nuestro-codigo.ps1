# DEPLOY DE NUESTRO CODIGO COMPLETO
Write-Host "Desplegando NUESTRO Simona Music completo..." -ForegroundColor Green

$deployScript = @"
cd /var
rm -rf azuracast
echo 'Clonando NUESTRO codigo personalizado...'
git clone https://github.com/CeferinSoftware/simona-music.git azuracast
cd azuracast
echo 'Configurando para produccion...'
cp docker-compose.production.yml docker-compose.yml
cp azuracast.production.env azuracast.env
cp azuracast.production.env .env
echo 'Iniciando Simona Music...'
docker compose up -d
echo 'Esperando servicios...'
sleep 30
echo 'Configuracion inicial...'
docker compose exec -T web azuracast_cli azuracast:setup --init
echo 'Aplicando traducciones personalizadas...'
docker compose exec -T web azuracast_cli locale:import
echo 'Limpiando cache...'
docker compose exec -T web azuracast_cli cache:clear
echo 'Estado final:'
docker compose ps
echo ''
echo '🎉 SIMONA MUSIC COMPLETADO!'
echo '🌐 URL: http://155.138.174.57'
echo '👤 Usuario: admin@simonamusic.net'
echo '🔑 Password: myself15867D_'
"@

Write-Host "Ejecutando deployment..." -ForegroundColor Yellow
$deployScript | ssh root@155.138.174.57

Write-Host "¡SIMONA MUSIC DESPLEGADO CON EXITO!" -ForegroundColor Green

