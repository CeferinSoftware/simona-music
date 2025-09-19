# ========================================
# SIMONA MUSIC - DEPLOYMENT DESDE WINDOWS
# ========================================

Write-Host "🎵 Desplegando Simona Music en servidor Vultr..." -ForegroundColor Blue

# Variables
$SERVER_IP = "155.138.174.57"
$SERVER_USER = "root"
$SERVER_PASS = "?5BgP2Jfm)sycWo9"

# Crear script temporal para SSH
$sshScript = @"
#!/bin/bash

echo "🔍 Verificando AzuraCast existente..."

if [ -d '/var/azuracast' ]; then
    echo "✅ AzuraCast encontrado, adaptando para Simona Music..."
    cd /var/azuracast
    
    # Backup de configuración actual
    mkdir -p /root/backups
    cp azuracast.env /root/backups/azuracast.env.backup.`$(date +%Y%m%d_%H%M%S)
    
    # Detener servicios
    docker compose down
    
    # Nueva configuración para Simona Music
    cat > azuracast.env << 'EOF'
APPLICATION_ENV=production
LOG_LEVEL=notice
INIT_ADMIN_EMAIL=admin@simonamusic.net
INIT_ADMIN_PASSWORD=myself15867D_
INIT_BASE_URL=http://155.138.174.57
INIT_INSTANCE_NAME=Simona Music
TZ=Europe/Madrid
ENABLE_REDIS=true
ENABLE_ADVANCED_FEATURES=true
PREFER_RELEASE_BUILDS=true
CHECK_FOR_UPDATES=true
AZURACAST_HTTP_PORT=80
AZURACAST_HTTPS_PORT=443
AZURACAST_SFTP_PORT=2022
EOF

    # Reiniciar con nueva configuración
    docker compose up -d
    sleep 30
    
    # Setup inicial
    docker compose exec -T web azuracast_cli azuracast:setup --load-fixtures
    
    echo "Estado de servicios:"
    docker compose ps
    
else
    echo "❌ AzuraCast no encontrado en /var/azuracast"
    echo "🔍 Buscando en otras ubicaciones..."
    find / -name "docker-compose.yml" -path "*/azuracast*" 2>/dev/null | head -5
fi

echo "✅ Deployment completado!"
echo "🌐 Acceso: http://155.138.174.57"
echo "👤 Admin: admin@simonamusic.net / myself15867D_"
"@

# Guardar script temporal
$sshScript | Out-File -FilePath "temp_deploy_script.sh" -Encoding UTF8

Write-Host "📡 Conectando al servidor..." -ForegroundColor Yellow

# Ejecutar usando plink (PuTTY) si está disponible
if (Get-Command plink -ErrorAction SilentlyContinue) {
    Write-Host "Usando PuTTY plink..." -ForegroundColor Green
    echo y | plink -ssh -l $SERVER_USER -pw $SERVER_PASS $SERVER_IP "bash -s" < temp_deploy_script.sh
} else {
    Write-Host "PuTTY no encontrado. Usando método alternativo..." -ForegroundColor Yellow
    
    # Método alternativo usando OpenSSH de Windows
    $env:SSHPASS = $SERVER_PASS
    
    # Copiar script al servidor
    scp -o StrictHostKeyChecking=no temp_deploy_script.sh ${SERVER_USER}@${SERVER_IP}:/tmp/deploy.sh
    
    # Ejecutar script
    ssh -o StrictHostKeyChecking=no ${SERVER_USER}@${SERVER_IP} "chmod +x /tmp/deploy.sh && /tmp/deploy.sh"
}

# Limpiar archivo temporal
Remove-Item temp_deploy_script.sh -ErrorAction SilentlyContinue

Write-Host "🎉 ¡Deployment completado!" -ForegroundColor Green
Write-Host "🌐 Tu Simona Music está en: http://155.138.174.57" -ForegroundColor Cyan
Write-Host "👤 Usuario: admin@simonamusic.net" -ForegroundColor Yellow
Write-Host "🔑 Contraseña: myself15867D_" -ForegroundColor Yellow
