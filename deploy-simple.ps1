# SIMONA MUSIC - DEPLOYMENT SIMPLE
Write-Host "Desplegando Simona Music..." -ForegroundColor Blue

$SERVER_IP = "155.138.174.57"
$SERVER_USER = "root"

# Comando SSH directo
$sshCommand = @"
cd /var/azuracast 2>/dev/null || cd /root

if [ -d '/var/azuracast' ]; then
    echo 'AzuraCast encontrado'
    cd /var/azuracast
    docker compose down
    
    # Configurar para Simona Music
    cat > azuracast.env << 'EOFCONFIG'
APPLICATION_ENV=production
INIT_ADMIN_EMAIL=admin@simonamusic.net
INIT_ADMIN_PASSWORD=myself15867D_
INIT_BASE_URL=http://155.138.174.57
INIT_INSTANCE_NAME=Simona Music
TZ=Europe/Madrid
AZURACAST_HTTP_PORT=80
AZURACAST_HTTPS_PORT=443
AZURACAST_SFTP_PORT=2022
EOFCONFIG

    docker compose up -d
    sleep 20
    docker compose exec -T web azuracast_cli azuracast:setup --load-fixtures
    docker compose ps
else
    echo 'AzuraCast no encontrado en /var/azuracast'
    ls -la /var/ | grep azura
    ls -la /root/ | grep azura
fi
"@

Write-Host "Conectando al servidor..." -ForegroundColor Yellow

# Intentar conexión SSH
try {
    $sshCommand | ssh -o StrictHostKeyChecking=no root@155.138.174.57
    Write-Host "Deployment completado!" -ForegroundColor Green
    Write-Host "Acceso: http://155.138.174.57" -ForegroundColor Cyan
} catch {
    Write-Host "Error en conexion SSH. Usar manualmente:" -ForegroundColor Red
    Write-Host "ssh root@155.138.174.57" -ForegroundColor Yellow
}
