# SUBIR NUESTRO CODIGO DIRECTO - SIN COMPLICACIONES
Write-Host "Subiendo Simona Music completo..." -ForegroundColor Green

# Comando SSH que hace todo
$comando = @"
cd /var && rm -rf azuracast && mkdir azuracast && cd azuracast
git clone https://github.com/AzuraCast/AzuraCast.git .
git checkout stable
docker compose up -d
echo 'Esperando...'
sleep 30
docker compose exec -T web azuracast_cli azuracast:setup --init
echo 'LISTO: http://155.138.174.57'
"@

Write-Host "Ejecutando deployment..." -ForegroundColor Yellow
$comando | ssh root@155.138.174.57

Write-Host "Simona Music base instalado!" -ForegroundColor Green
Write-Host "Ahora aplicaremos el branding..." -ForegroundColor Yellow

