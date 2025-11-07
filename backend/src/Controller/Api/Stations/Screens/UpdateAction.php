<?php

declare(strict_types=1);

namespace App\Controller\Api\Stations\Screens;

use App\Container\EntityManagerAwareTrait;
use App\Controller\SingleActionInterface;
use App\Entity\Repository\StationScreenRepository;
use App\Exception\Http\NotFoundException;
use App\Http\Response;
use App\Http\ServerRequest;
use App\Utilities\Types;
use Psr\Http\Message\ResponseInterface;

final class UpdateAction implements SingleActionInterface
{
    use EntityManagerAwareTrait;

    public function __construct(
        private readonly StationScreenRepository $screenRepo
    ) {
    }

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $screenId = Types::int($params['screen_id']);
        $station = $request->getStation();

        $screen = $this->screenRepo->findOneBy([
            'id' => $screenId,
            'station' => $station
        ]);

        if (!$screen) {
            throw NotFoundException::generic();
        }

        $parsedBody = (array)$request->getParsedBody();

        if (isset($parsedBody['name'])) {
            $screen->name = $parsedBody['name'];
        }
        
        if (isset($parsedBody['description'])) {
            $screen->description = $parsedBody['description'];
        }
        
        if (isset($parsedBody['is_active'])) {
            $screen->is_active = (bool)$parsedBody['is_active'];
        }
        
        if (isset($parsedBody['content_type'])) {
            $screen->content_type = $parsedBody['content_type'];
        }
        
        if (isset($parsedBody['metadata'])) {
            $screen->metadata = $parsedBody['metadata'];
        }

        $screen->update();
        
        $this->em->persist($screen);
        $this->em->flush();

        return $response->withJson([
            'success' => true,
            'message' => __('Screen updated successfully.'),
        ]);
    }
}
