<?php

declare(strict_types=1);

namespace App\Controller\Api\Stations\Screens;

use App\Controller\SingleActionInterface;
use App\Entity\Repository\StationScreenRepository;
use App\Http\Response;
use App\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final readonly class ListAction implements SingleActionInterface
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
        $screens = $this->screenRepo->fetchForStation($station);

        $router = $request->getRouter();
        $baseUrl = $router->getBaseUrl();

        $result = [];
        foreach ($screens as $screen) {
            $result[] = [
                'id' => $screen->getIdRequired(),
                'name' => $screen->name,
                'description' => $screen->description,
                'is_active' => $screen->is_active,
                'content_type' => $screen->content_type,
                'metadata' => $screen->metadata,
                'public_url' => $screen->getPublicUrl($baseUrl),
                'created_at' => $screen->created_at->getTimestamp(),
                'updated_at' => $screen->updated_at->getTimestamp(),
            ];
        }

        return $response->withJson($result);
    }
}
