# COMPLETAR INSTALACION DE SIMONA MUSIC
Write-Host "Completando instalacion de Simona Music..." -ForegroundColor Blue

$commands = @"
cd /var/azuracast
echo 'Iniciando servicios...'
docker compose up -d
echo 'Esperando 30 segundos...'
sleep 30
echo 'Configuracion inicial...'
docker compose exec -T web azuracast_cli azuracast:setup --init
echo 'Estado final:'
docker compose ps
echo 'LISTO! Simona Music en http://155.138.174.57'
echo 'Usuario: admin@simonamusic.net'
echo 'Password: myself15867D_'
"@

$commands | ssh root@155.138.174.57

Write-Host "Instalacion completada!" -ForegroundColor Green

