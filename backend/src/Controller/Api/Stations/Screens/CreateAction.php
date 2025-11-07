<?php

declare(strict_types=1);

namespace App\Controller\Api\Stations\Screens;

use App\Container\EntityManagerAwareTrait;
use App\Controller\SingleActionInterface;
use App\Entity\StationScreen;
use App\Http\Response;
use App\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final class CreateAction implements SingleActionInterface
{
    use EntityManagerAwareTrait;

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $station = $request->getStation();
        $parsedBody = (array)$request->getParsedBody();

        $screen = new StationScreen($station, $parsedBody['name']);
        
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

        $this->em->persist($screen);
        $this->em->flush();

        return $response->withJson([
            'success' => true,
            'message' => __('Screen created successfully.'),
            'id' => $screen->getIdRequired(),
        ]);
    }
}
