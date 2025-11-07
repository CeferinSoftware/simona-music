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

final class DeleteAction implements SingleActionInterface
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

        $this->em->remove($screen);
        $this->em->flush();

        return $response->withJson([
            'success' => true,
            'message' => __('Screen deleted successfully.'),
        ]);
    }
}
