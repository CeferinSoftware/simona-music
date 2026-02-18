<?php

declare(strict_types=1);

namespace App\Controller\Api\Frontend;

use App\Acl;
use App\Container\EntityManagerAwareTrait;
use App\Controller\SingleActionInterface;
use App\Entity\Repository\StationRepository;
use App\Entity\Station;
use App\Enums\StationPermissions;
use App\Http\Response;
use App\Http\ServerRequest;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;

/**
 * API endpoint that returns the list of stations (terrazas) where the 
 * authenticated user has Streamers permission for the centralized DJ panel.
 */
#[OA\Get(
    path: '/frontend/dj/terrazas',
    operationId: 'getDjTerrazas',
    summary: 'Get DJ Available Terrazas',
    description: 'Returns a list of stations where the authenticated user can stream as a DJ',
    security: [['BearerAuth' => []]],
    tags: ['DJ Panel'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of available terrazas',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'short_name', type: 'string'),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
                        new OA\Property(property: 'is_online', type: 'boolean'),
                        new OA\Property(property: 'province', type: 'string', nullable: true),
                        new OA\Property(property: 'city', type: 'string', nullable: true),
                        new OA\Property(property: 'sector', type: 'string', nullable: true),
                        new OA\Property(property: 'webdj_url', type: 'string'),
                        new OA\Property(property: 'public_page_url', type: 'string'),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
    ]
)]
final class DjTerrazasAction implements SingleActionInterface
{
    use EntityManagerAwareTrait;

    public function __construct(
        private readonly StationRepository $stationRepo,
        private readonly Acl $acl
    ) {
    }

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $user = $request->getUser();
        
        if ($user === null) {
            return $response->withStatus(401)
                ->withJson(['error' => 'Not authenticated']);
        }

        $router = $request->getRouter();
        $terrazas = [];

        // Get all enabled stations
        foreach ($this->stationRepo->iterateEnabledStations() as $station) {
            // Check if user has Streamers permission for this station
            if ($this->acl->userAllowed(StationPermissions::Streamers, $user, $station)) {
                $terrazas[] = $this->buildTerrazaInfo($station, $router);
            }
        }

        return $response->withJson($terrazas);
    }

    private function buildTerrazaInfo(Station $station, $router): array
    {
        // Check if station is online by looking at frontend/backend status
        $isOnline = $station->hasLocalServices();

        return [
            'id' => $station->id,
            'name' => $station->name,
            'short_name' => $station->short_name,
            'description' => $station->description,
            'is_online' => $isOnline,
            'province' => $station->province ?? null,
            'city' => $station->city ?? null,
            'sector' => $station->sector ?? null,
            'webdj_url' => (string)$router->named(
                'stations:index:webdj',
                ['station_id' => $station->short_name]
            ),
            'public_page_url' => (string)$router->named(
                'public:index',
                ['station_id' => $station->short_name]
            ),
        ];
    }
}
