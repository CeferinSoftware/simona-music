#!/bin/bash
# === DEPLOY SIMONA MUSIC ===
echo '🔑 Copiando clave SSH para acceso futuro...'
mkdir -p ~/.ssh
echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAID2wPFuFlpbYStk+kjFqCHTKJZtf6vCWJ6/gPW2/QTcA ceferin@DESKTOP-GHE5D3F' >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

echo '📥 Pulling latest code...'
cd /root/simona-music
git pull origin main

echo '📋 Copying production config...'
cp -f docker-compose.production.yml docker-compose.yml

echo '🛑 Stopping containers...'
docker compose down

echo '🧹 Cleaning build cache...'
docker builder prune -af

echo '🔨 Building new image...'
docker compose build --no-cache web

echo '🚀 Starting containers...'
docker compose up -d

echo '⏱️ Waiting for services...'
sleep 20

echo '📊 Running migrations...'
docker compose exec -T web azuracast_cli migrations:migrate --no-interaction --allow-no-migration

echo '🧹 Clearing cache...'
docker compose exec -T web azuracast_cli cache:clear

echo '✅ Deploy completado!'
docker compose ps