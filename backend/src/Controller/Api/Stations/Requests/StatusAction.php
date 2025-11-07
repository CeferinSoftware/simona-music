<?php

declare(strict_types=1);

namespace App\Controller\Api\Stations\Requests;

use App\Container\EntityManagerAwareTrait;
use App\Controller\SingleActionInterface;
use App\Entity\Repository\StationRequestRepository;
use App\Exception\Http\NotFoundException;
use App\Http\Response;
use App\Http\ServerRequest;
use App\OpenApi;
use App\Utilities\Types;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;

#[
    OA\Get(
        path: '/station/{station_id}/request-status/{request_id}',
        operationId: 'getRequestStatus',
        summary: 'Get the status of a specific song request.',
        security: [],
        tags: [OpenApi::TAG_PUBLIC_STATIONS],
        parameters: [
            new OA\Parameter(ref: OpenApi::REF_STATION_ID_REQUIRED),
            new OA\Parameter(
                name: 'request_id',
                description: 'The request ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OpenApi\Response\Success(),
            new OpenApi\Response\NotFound(),
        ]
    )
]
final class StatusAction implements SingleActionInterface
{
    use EntityManagerAwareTrait;

    public function __construct(
        private readonly StationRequestRepository $requestRepo
    ) {
    }

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $requestId = Types::int($params['request_id']);
        $station = $request->getStation();

        $stationRequest = $this->requestRepo->findOneBy([
            'id' => $requestId,
            'station' => $station
        ]);

        if (!$stationRequest) {
            throw NotFoundException::generic();
        }

        return $response->withJson([
            'id' => $stationRequest->getIdRequired(),
            'status' => $stationRequest->getStatus(),
            'track_title' => $stationRequest->track->getTitle(),
            'track_artist' => $stationRequest->track->getArtist(),
            'requester_name' => $stationRequest->requester_name,
            'requester_avatar' => $stationRequest->requester_avatar,
            'comment' => $stationRequest->comment,
            'timestamp' => $stationRequest->timestamp->getTimestamp(),
            'played_at' => $stationRequest->played_at?->getTimestamp(),
        ]);
    }
}
