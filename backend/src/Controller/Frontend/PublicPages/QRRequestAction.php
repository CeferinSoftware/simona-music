<?php

declare(strict_types=1);

namespace App\Controller\Frontend\PublicPages;

use App\Controller\SingleActionInterface;
use App\Exception\NotFoundException;
use App\Http\Response;
use App\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final readonly class QRRequestAction implements SingleActionInterface
{
    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $station = $request->getStation();

        if (!$station->enable_public_page) {
            throw NotFoundException::station();
        }

        $view = $request->getView();

        // Add station public code.
        $view->fetch(
            'frontend/public/partials/station-custom',
            ['station' => $station]
        );

        return $view->renderVuePage(
            response: $response
                ->withHeader('X-Frame-Options', '*'),
            component: 'Public/Requests/QRRequestPage',
            id: 'qr-song-requests',
            layout: 'minimal',
            title: __('Request a Song') . ' - ' . $station->name,
            layoutParams: [
                'page_class' => 'qr-request station-' . $station->short_name,
                'hide_footer' => true,
            ],
            props: [
                'station' => [
                    'id' => $station->id,
                    'name' => $station->name,
                ],
            ],
        );
    }
}
