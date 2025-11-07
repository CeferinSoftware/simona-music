<?php

declare(strict_types=1);

namespace App\Controller\Frontend\PublicPages;

use App\Controller\SingleActionInterface;
use App\Entity\Repository\StationScreenRepository;
use App\Exception\NotFoundException;
use App\Http\Response;
use App\Http\ServerRequest;
use App\Utilities\Types;
use Psr\Http\Message\ResponseInterface;

final readonly class ScreenAction implements SingleActionInterface
{
    public function __construct(
        private StationScreenRepository $screenRepo
    ) {
    }

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $station = $request->getStation();
        $screenId = Types::int($params['screen_id']);

        $screen = $this->screenRepo->findOneBy([
            'id' => $screenId,
            'station' => $station,
        ]);

        if (!$screen || !$screen->is_active) {
            throw NotFoundException::generic();
        }

        $view = $request->getView();

        // Add station public code (for branding).
        $view->fetch(
            'frontend/public/partials/station-custom',
            ['station' => $station]
        );

        // Render different components based on content type
        $component = match ($screen->content_type) {
            'nowplaying' => 'Public/Screen/NowPlaying',
            'requests' => 'Public/Screen/Requests',
            'custom' => 'Public/Screen/Custom',
            default => 'Public/Screen/NowPlaying',
        };

        return $view->renderVuePage(
            response: $response
                ->withHeader('X-Frame-Options', '*'),
            component: $component,
            id: 'screen-display',
            layout: 'minimal',
            title: $screen->name . ' - ' . $station->name,
            layoutParams: [
                'page_class' => 'screen-display station-' . $station->short_name,
                'hide_footer' => true,
            ],
            props: [
                'screen' => [
                    'id' => $screen->getIdRequired(),
                    'name' => $screen->name,
                    'content_type' => $screen->content_type,
                    'metadata' => $screen->metadata,
                ],
                'station' => [
                    'id' => $station->id,
                    'name' => $station->name,
                ],
            ],
        );
    }
}
