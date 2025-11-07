<template>
    <div class="qr-scanner-widget">
        <div class="qr-content">
            <div class="qr-code">
                <img :src="qrCodeUrl" alt="QR Code" />
            </div>
            <div class="qr-text">
                <p>{{ $gettext('Escanea y pide tu canción') }}</p>
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

// Generate QR code using a QR code API service
const qrCodeUrl = computed(() => {
    const encodedUrl = encodeURIComponent(props.requestUrl);
    // Using qrserver.com API (free, no API key needed)
    return `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodedUrl}`;
});
</script>

<style scoped>
.qr-scanner-widget {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    border: 2px solid rgba(255, 255, 255, 0.1);
    animation: fadeInScale 0.6s cubic-bezier(0.16, 1, 0.3, 1);
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
    transform: scale(1.05);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.qr-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.qr-code {
    background: white;
    padding: 8px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.qr-code img {
    display: block;
    width: 150px;
    height: 150px;
    border-radius: 8px;
}

.qr-text {
    text-align: center;
}

.qr-text p {
    margin: 0;
    color: white;
    font-size: 14px;
    font-weight: 600;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    letter-spacing: 0.5px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .qr-scanner-widget {
        top: 10px;
        right: 10px;
        padding: 12px;
    }
    
    .qr-code img {
        width: 120px;
        height: 120px;
    }
    
    .qr-text p {
        font-size: 12px;
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
