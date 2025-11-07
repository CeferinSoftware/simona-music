<?php

declare(strict_types=1);

namespace App\Controller\Api\Stations\Vue;

use App\Container\EntityManagerAwareTrait;
use App\Controller\SingleActionInterface;
use App\Entity\Enums\PlaylistSources;
use App\Http\Response;
use App\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final class CatalogoAction implements SingleActionInterface
{
    use EntityManagerAwareTrait;

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $station = $request->getStation();

        // Obtener playlists de la estación
        $playlists = $this->em->createQuery(
            <<<'DQL'
                SELECT sp.id, sp.name
                FROM App\Entity\StationPlaylist sp
                WHERE sp.station = :station AND sp.source = :source
                ORDER BY sp.name ASC
            DQL
        )->setParameter('station', $station)
            ->setParameter('source', PlaylistSources::Songs->value)
            ->getArrayResult();

        return $response->withJson([
            'playlists' => $playlists,
            'stationId' => $station->getId(),
            'stationName' => $station->getName()
        ]);
    }
}
