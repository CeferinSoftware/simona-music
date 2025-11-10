<template>
    <div class="qr-scanner-widget">
        <div class="qr-content">
            <div class="qr-code">
                <img :src="qrCodeUrl" alt="QR Code" />
            </div>
            <div class="qr-text-container">
                <div class="qr-header">
                    <span class="qr-icon">📱</span>
                    <h3 class="qr-title">{{ $gettext('Escanea el QR') }}</h3>
                </div>
                <p class="qr-description">{{ $gettext('y pide tu canción') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface QRWidgetProps {
    requestUrl: string;
}

const props = defineProps<QRWidgetProps>();

console.error('🔍 QR Widget - requestUrl:', props.requestUrl);

// Generate QR code using a QR code API service
const qrCodeUrl = computed(() => {
    const encodedUrl = encodeURIComponent(props.requestUrl);
    const url = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodedUrl}`;
    console.error('🔍 QR Widget - qrCodeUrl:', url);
    return url;
});
</script>

<style>
.qr-scanner-widget {
    position: fixed !important;
    top: 20px !important;
    right: 20px !important;
    z-index: 99999 !important;
    pointer-events: auto !important;
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.95) 100%);
    backdrop-filter: blur(16px);
    border-radius: 16px;
    padding: 14px 18px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5),
                0 0 1px rgba(255, 255, 255, 0.1) inset;
    border: 2px solid rgba(59, 130, 246, 0.3);
    animation: fadeInScale 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeInScale {
    0% {
        opacity: 0;
        transform: scale(0.8) translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.qr-scanner-widget:hover {
    transform: scale(1.05) translateY(-2px);
    box-shadow: 0 25px 70px rgba(59, 130, 246, 0.3),
                0 20px 60px rgba(0, 0, 0, 0.6);
    border-color: rgba(59, 130, 246, 0.5);
}

.qr-content {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 16px;
}

.qr-text-container {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.qr-header {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
}

.qr-icon {
    font-size: 24px;
    animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

.qr-title {
    margin: 0;
    color: white;
    font-size: 14px;
    font-weight: 700;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    white-space: nowrap;
}

.qr-code {
    background: white;
    padding: 8px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3),
                0 0 0 3px rgba(59, 130, 246, 0.2);
    flex-shrink: 0;
}

.qr-code img {
    display: block;
    width: 100px;
    height: 100px;
    border-radius: 8px;
}

.qr-description {
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
    font-size: 13px;
    font-weight: 500;
    text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
    letter-spacing: 0.3px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .qr-scanner-widget {
        top: 10px;
        right: 10px;
        padding: 16px;
    }
    
    .qr-icon {
        font-size: 24px;
    }
    
    .qr-title {
        font-size: 14px;
    }
    
    .qr-code img {
        width: 130px;
        height: 130px;
    }
    
    .qr-description {
        font-size: 13px;
    }
}

/* Pulse animation every 60 seconds */
.qr-scanner-widget {
    animation: fadeInScale 0.6s cubic-bezier(0.16, 1, 0.3, 1), 
               pulse 60s ease-in-out infinite;
}

@keyframes pulse {
    0%, 98% {
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    }
    99% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7),
                    0 0 0 10px rgba(59, 130, 246, 0.4),
                    0 0 0 20px rgba(59, 130, 246, 0.1),
                    0 8px 32px rgba(0, 0, 0, 0.4);
    }
    100% {
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    }
}
</style>
