#!/bin/bash

# Script para probar conexión al servidor
echo "🔍 Probando conexión al servidor 162.249.169.26..."

SERVER_IP="162.249.169.26"

echo "📡 Intentando conexión SSH..."
echo "IP: $SERVER_IP"
echo "Usuario: root"
echo "Puerto: 22"

# Probar conectividad básica
echo "🏓 Probando conectividad (ping)..."
ping -c 3 $SERVER_IP

echo ""
echo "🔐 Probando puerto SSH..."
nc -zv $SERVER_IP 22

echo ""
echo "📋 Para conectar manualmente usa:"
echo "ssh root@$SERVER_IP"
echo ""
echo "Si no tienes la contraseña, búscala en:"
echo "1. Email de bienvenida del proveedor"
echo "2. Panel del proveedor de hosting"
echo "3. Sección 'Server Details' o 'SSH Access'"
