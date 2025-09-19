# ============================================
# DEPLOY PERSONALIZADO DE SIMONA MUSIC
# ============================================

Write-Host "🎵 Desplegando Simona Music personalizado..." -ForegroundColor Blue

$SERVER_IP = "155.138.174.57"
$SERVER_USER = "root"

Write-Host "📦 Creando paquete con nuestro código..." -ForegroundColor Yellow

# Crear archivo con nuestras personalizaciones
$customFiles = @(
    "frontend\components\Layout\DashboardLayout.vue",
    "frontend\components\Login.vue", 
    "frontend\components\Common\PanelFooter.vue",
    "frontend\components\Setup\Register.vue",
    "frontend\components\Admin\Branding\BrandingForm.vue",
    "frontend\entities\ApiInterfaces.ts",
    "translations\es_ES.UTF-8\LC_MESSAGES\default.po",
    "backend\templates\minimal.phtml",
    "docker-compose.production.yml",
    "azuracast.production.env"
)

# Crear script de deployment
$deployScript = @"
#!/bin/bash
echo '🗑️ Limpiando instalación anterior...'
cd /var/azuracast 2>/dev/null && docker compose down 2>/dev/null
cd /var && rm -rf azuracast

echo '📥 Clonando AzuraCast base...'
cd /var
git clone https://github.com/AzuraCast/AzuraCast.git azuracast
cd azuracast
git checkout stable

echo '⚙️ Configurando para Simona Music...'
# Configuración
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

# Docker compose
cp docker-compose.sample.yml docker-compose.yml

echo '🚀 Iniciando Simona Music...'
docker compose up -d

echo '⏱️ Esperando servicios...'
sleep 30

echo '🔧 Configuración inicial...'
docker compose exec -T web azuracast_cli azuracast:setup --init

echo '📊 Estado:'
docker compose ps

echo '✅ Simona Music base instalado!'
"@

Write-Host "🌐 Conectando al servidor..." -ForegroundColor Yellow

# Ejecutar deployment
try {
    $deployScript | ssh root@155.138.174.57 "cat > /tmp/deploy.sh && chmod +x /tmp/deploy.sh && /tmp/deploy.sh"
    Write-Host "✅ Deployment completado!" -ForegroundColor Green
    Write-Host "🌐 Simona Music disponible en: http://155.138.174.57" -ForegroundColor Cyan
    Write-Host "👤 Usuario: admin@simonamusic.net" -ForegroundColor Yellow
    Write-Host "🔑 Contraseña: myself15867D_" -ForegroundColor Yellow
} catch {
    Write-Host "❌ Error en deployment" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}

