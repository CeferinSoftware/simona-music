<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Controller\Api\AbstractApiCrudController;
use App\Controller\Api\Traits\CanSearchResults;
use App\Controller\Api\Traits\CanSortResults;
use App\Entity\Advertisement;
use App\Entity\AdvertisementCategory;
use App\Entity\AdvertisementLocation;
use App\Entity\Enums\AdCategories;
use App\Entity\Enums\AdMediaType;
use App\Entity\Enums\AdStatus;
use App\Entity\Repository\AdvertisementRepository;
use App\Http\Response;
use App\Http\ServerRequest;
use App\OpenApi;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;

#[
    OA\Get(
        path: '/admin/advertisements',
        operationId: 'adminGetAdvertisements',
        description: 'Lista todos los anuncios del sistema.',
        security: OpenApi::API_KEY_SECURITY,
        tags: ['Administration: Advertisements'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Advertisement')
                )
            ),
            new OA\Response(ref: OpenApi::REF_RESPONSE_ACCESS_DENIED, response: 403),
            new OA\Response(ref: OpenApi::REF_RESPONSE_NOT_FOUND, response: 404),
            new OA\Response(ref: OpenApi::REF_RESPONSE_GENERIC_ERROR, response: 500),
        ]
    ),
    OA\Post(
        path: '/admin/advertisements',
        operationId: 'adminAddAdvertisement',
        description: 'Crea un nuevo anuncio.',
        security: OpenApi::API_KEY_SECURITY,
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: '#/components/schemas/Advertisement')
        ),
        tags: ['Administration: Advertisements'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(ref: '#/components/schemas/Advertisement')
            ),
            new OA\Response(ref: OpenApi::REF_RESPONSE_ACCESS_DENIED, response: 403),
            new OA\Response(ref: OpenApi::REF_RESPONSE_GENERIC_ERROR, response: 500),
        ]
    ),
    OA\Get(
        path: '/admin/advertisements/{id}',
        operationId: 'adminGetAdvertisement',
        description: 'Obtiene un anuncio específico.',
        security: OpenApi::API_KEY_SECURITY,
        tags: ['Administration: Advertisements'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Advertisement ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', format: 'int64')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(ref: '#/components/schemas/Advertisement')
            ),
            new OA\Response(ref: OpenApi::REF_RESPONSE_ACCESS_DENIED, response: 403),
            new OA\Response(ref: OpenApi::REF_RESPONSE_NOT_FOUND, response: 404),
            new OA\Response(ref: OpenApi::REF_RESPONSE_GENERIC_ERROR, response: 500),
        ]
    ),
    OA\Put(
        path: '/admin/advertisements/{id}',
        operationId: 'adminEditAdvertisement',
        description: 'Actualiza un anuncio existente.',
        security: OpenApi::API_KEY_SECURITY,
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: '#/components/schemas/Advertisement')
        ),
        tags: ['Administration: Advertisements'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Advertisement ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', format: 'int64')
            ),
        ],
        responses: [
            new OA\Response(ref: OpenApi::REF_RESPONSE_SUCCESS, response: 200),
            new OA\Response(ref: OpenApi::REF_RESPONSE_ACCESS_DENIED, response: 403),
            new OA\Response(ref: OpenApi::REF_RESPONSE_NOT_FOUND, response: 404),
            new OA\Response(ref: OpenApi::REF_RESPONSE_GENERIC_ERROR, response: 500),
        ]
    ),
    OA\Delete(
        path: '/admin/advertisements/{id}',
        operationId: 'adminDeleteAdvertisement',
        description: 'Elimina un anuncio.',
        security: OpenApi::API_KEY_SECURITY,
        tags: ['Administration: Advertisements'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Advertisement ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', format: 'int64')
            ),
        ],
        responses: [
            new OA\Response(ref: OpenApi::REF_RESPONSE_SUCCESS, response: 200),
            new OA\Response(ref: OpenApi::REF_RESPONSE_ACCESS_DENIED, response: 403),
            new OA\Response(ref: OpenApi::REF_RESPONSE_NOT_FOUND, response: 404),
            new OA\Response(ref: OpenApi::REF_RESPONSE_GENERIC_ERROR, response: 500),
        ]
    )
]
final class AdvertisementsController extends AbstractApiCrudController
{
    use CanSearchResults;
    use CanSortResults;

    protected string $entityClass = Advertisement::class;
    protected string $resourceRouteName = 'api:admin:advertisement';

    public function __construct(
        private readonly AdvertisementRepository $advertisementRepo
    ) {
    }

    public function listAction(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $qb = $this->em->createQueryBuilder()
            ->select('a')
            ->from(Advertisement::class, 'a')
            ->orderBy('a.priority', 'DESC')
            ->addOrderBy('a.created_at', 'DESC');

        $qb = $this->sortQueryBuilder(
            $request,
            $qb,
            [
                'name' => 'a.name',
                'status' => 'a.status',
                'priority' => 'a.priority',
                'play_count' => 'a.play_count',
                'created_at' => 'a.created_at',
            ],
            'a.priority',
            'DESC'
        );

        $qb = $this->searchQueryBuilder(
            $request,
            $qb,
            [
                'a.name',
                'a.description',
                'a.advertiser_name',
            ]
        );

        return $this->listPaginatedFromQuery($request, $response, $qb->getQuery());
    }

    public function getAction(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $record = $this->getRecord($params);

        if ($record === null) {
            return $response->withStatus(404)
                ->withJson(['error' => 'Anuncio no encontrado']);
        }

        return $response->withJson($this->toArray($record));
    }

    public function createAction(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $data = (array)$request->getParsedBody();

        $ad = new Advertisement();
        $this->fromArray($ad, $data);
        
        // Procesar categorías
        if (isset($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $categoryValue) {
                $category = AdCategories::tryFrom($categoryValue);
                if ($category !== null) {
                    $ad->addCategory($category);
                }
            }
        }

        // Procesar ubicaciones
        if (isset($data['locations']) && is_array($data['locations'])) {
            foreach ($data['locations'] as $locationData) {
                if (isset($locationData['province'])) {
                    $ad->addLocation(
                        $locationData['province'],
                        $locationData['city'] ?? null,
                        $locationData['sector'] ?? null
                    );
                }
            }
        }

        $this->em->persist($ad);
        $this->em->flush();

        return $response->withJson($this->toArray($ad));
    }

    public function editAction(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $record = $this->getRecord($params);

        if ($record === null) {
            return $response->withStatus(404)
                ->withJson(['error' => 'Anuncio no encontrado']);
        }

        $data = (array)$request->getParsedBody();
        $this->fromArray($record, $data);

        // Actualizar categorías
        if (isset($data['categories']) && is_array($data['categories'])) {
            $record->clearCategories();
            foreach ($data['categories'] as $categoryValue) {
                $category = AdCategories::tryFrom($categoryValue);
                if ($category !== null) {
                    $record->addCategory($category);
                }
            }
        }

        // Actualizar ubicaciones
        if (isset($data['locations']) && is_array($data['locations'])) {
            $record->clearLocations();
            foreach ($data['locations'] as $locationData) {
                if (isset($locationData['province'])) {
                    $record->addLocation(
                        $locationData['province'],
                        $locationData['city'] ?? null,
                        $locationData['sector'] ?? null
                    );
                }
            }
        }

        $this->em->persist($record);
        $this->em->flush();

        return $response->withJson($this->toArray($record));
    }

    public function deleteAction(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $record = $this->getRecord($params);

        if ($record === null) {
            return $response->withStatus(404)
                ->withJson(['error' => 'Anuncio no encontrado']);
        }

        $this->em->remove($record);
        $this->em->flush();

        return $response->withJson(['success' => true]);
    }

    /**
     * @param Advertisement $record
     */
    protected function toArray(object $record, array $context = []): array
    {
        $categories = [];
        foreach ($record->categories as $cat) {
            $categories[] = $cat->category->value;
        }

        $locations = [];
        foreach ($record->locations as $loc) {
            $locations[] = [
                'id' => $loc->id,
                'province' => $loc->province,
                'city' => $loc->city,
                'sector' => $loc->sector,
                'full_location' => $loc->getFullLocation(),
            ];
        }

        return [
            'id' => $record->id,
            'name' => $record->name,
            'description' => $record->description,
            'media_type' => $record->media_type->value,
            'media_path' => $record->media_path,
            'media_url' => $record->media_url,
            'duration' => $record->duration,
            'status' => $record->status->value,
            'priority' => $record->priority,
            'advertiser_name' => $record->advertiser_name,
            'start_date' => $record->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $record->end_date?->format('Y-m-d H:i:s'),
            'max_plays' => $record->max_plays,
            'play_count' => $record->play_count,
            'play_frequency' => $record->play_frequency,
            'time_start' => $record->time_start,
            'time_end' => $record->time_end,
            'active_days' => $record->active_days,
            'categories' => $categories,
            'locations' => $locations,
            'created_at' => $record->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $record->updated_at->format('Y-m-d H:i:s'),
            'links' => [
                'self' => $this->router->fromHere($this->resourceRouteName, ['id' => $record->id]),
            ],
        ];
    }

    /**
     * @param Advertisement $record
     */
    protected function fromArray(object $record, array $data): void
    {
        if (isset($data['name'])) {
            $record->name = $data['name'];
        }
        if (isset($data['description'])) {
            $record->description = $data['description'];
        }
        if (isset($data['media_type'])) {
            $record->media_type = AdMediaType::tryFrom($data['media_type']) ?? AdMediaType::Audio;
        }
        if (isset($data['media_path'])) {
            $record->media_path = $data['media_path'];
        }
        if (isset($data['media_url'])) {
            $record->media_url = $data['media_url'];
        }
        if (isset($data['duration'])) {
            $record->duration = (float)$data['duration'];
        }
        if (isset($data['status'])) {
            $record->status = AdStatus::tryFrom($data['status']) ?? AdStatus::Active;
        }
        if (isset($data['priority'])) {
            $record->priority = (int)$data['priority'];
        }
        if (isset($data['advertiser_name'])) {
            $record->advertiser_name = $data['advertiser_name'];
        }
        if (isset($data['start_date']) && !empty($data['start_date'])) {
            $record->start_date = new \DateTime($data['start_date']);
        }
        if (isset($data['end_date']) && !empty($data['end_date'])) {
            $record->end_date = new \DateTime($data['end_date']);
        }
        if (isset($data['max_plays'])) {
            $record->max_plays = (int)$data['max_plays'];
        }
        if (isset($data['play_frequency'])) {
            $record->play_frequency = (int)$data['play_frequency'];
        }
        if (isset($data['time_start'])) {
            $record->time_start = $data['time_start'];
        }
        if (isset($data['time_end'])) {
            $record->time_end = $data['time_end'];
        }
        if (isset($data['active_days'])) {
            $record->active_days = $data['active_days'];
        }
    }

    private function getRecord(array $params): ?Advertisement
    {
        $id = (int)($params['id'] ?? 0);
        return $this->em->find(Advertisement::class, $id);
    }
}
