<?php

declare(strict_types=1);

namespace App\Notification\Check;

use App\Container\EnvironmentAwareTrait;
use App\Entity\Api\Notification;
use App\Enums\FlashLevels;
use App\Event\GetNotifications;
use App\Exception\Http\RateLimitExceededException;

final class DonateAdvisorCheck
{
    use EnvironmentAwareTrait;

    public function __invoke(GetNotifications $event): void
    {
        // Ocultar por completo la notificación de donaciones en esta distribución.
        // Se mantiene el método por compatibilidad, pero no añade notificaciones.
        return;
    }
}
