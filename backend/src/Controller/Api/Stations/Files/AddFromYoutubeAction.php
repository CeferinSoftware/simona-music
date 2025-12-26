<?php

declare(strict_types=1);

namespace App\Controller\Api\Stations\Files;

use App\Container\EntityManagerAwareTrait;
use App\Container\LoggerAwareTrait;
use App\Controller\SingleActionInterface;
use App\Entity\Api\Error;
use App\Entity\Api\Status;
use App\Entity\StationMedia;
use App\Http\Response;
use App\Http\ServerRequest;
use App\OpenApi;
use App\Utilities\Types;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;

#[
    OA\Post(
        path: '/station/{station_id}/files/youtube',
        operationId: 'postAddFromYoutube',
        summary: 'Add a YouTube video as a media entry.',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'youtube_url', type: 'string', description: 'YouTube video URL'),
                    new OA\Property(property: 'title', type: 'string', description: 'Song title'),
                    new OA\Property(property: 'artist', type: 'string', description: 'Artist name'),
                    new OA\Property(property: 'genre', type: 'string', description: 'Genre'),
                    new OA\Property(property: 'album', type: 'string', description: 'Album name'),
                ]
            )
        ),
        tags: [OpenApi::TAG_STATIONS_MEDIA],
        parameters: [
            new OA\Parameter(ref: OpenApi::REF_STATION_ID_REQUIRED),
        ],
        responses: [
            new OpenApi\Response\Success(),
            new OpenApi\Response\AccessDenied(),
            new OpenApi\Response\NotFound(),
            new OpenApi\Response\GenericError(),
        ]
    )
]
final class AddFromYoutubeAction implements SingleActionInterface
{
    use LoggerAwareTrait;
    use EntityManagerAwareTrait;

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $station = $request->getStation();
        $mediaStorage = $station->media_storage_location;

        $youtubeUrl = Types::stringOrNull($request->getParam('youtube_url'));
        $title = Types::stringOrNull($request->getParam('title'));
        $artist = Types::stringOrNull($request->getParam('artist'));
        $genre = Types::stringOrNull($request->getParam('genre'));
        $album = Types::stringOrNull($request->getParam('album'));

        if (empty($youtubeUrl)) {
            return $response->withStatus(400)->withJson(
                new Error(400, 'La URL de YouTube es requerida.')
            );
        }

        if (empty($title)) {
            return $response->withStatus(400)->withJson(
                new Error(400, 'El título es requerido.')
            );
        }

        // Validar URL de YouTube
        $videoId = $this->extractYouTubeVideoId($youtubeUrl);
        if ($videoId === null) {
            return $response->withStatus(400)->withJson(
                new Error(400, 'La URL de YouTube no es válida.')
            );
        }

        // Normalizar la URL de YouTube
        $normalizedUrl = 'https://www.youtube.com/watch?v=' . $videoId;

        // Verificar si ya existe un media con esta URL
        $existingMedia = $this->em->createQueryBuilder()
            ->select('m')
            ->from(StationMedia::class, 'm')
            ->where('m.storage_location = :storage')
            ->andWhere('m.video_url = :url')
            ->setParameter('storage', $mediaStorage)
            ->setParameter('url', $normalizedUrl)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingMedia !== null) {
            return $response->withStatus(400)->withJson(
                new Error(400, 'Este vídeo de YouTube ya existe en la biblioteca.')
            );
        }

        // Crear una entrada virtual para el media de YouTube
        // El path será un identificador especial que indica que es contenido de YouTube
        $virtualPath = '_youtube/' . $videoId . '.youtube';

        $stationMedia = new StationMedia($mediaStorage, $virtualPath);
        $stationMedia->title = $title;
        $stationMedia->artist = $artist ?? '';
        $stationMedia->genre = $genre;
        $stationMedia->album = $album;
        $stationMedia->video_url = $normalizedUrl;
        
        // Establecer una duración estimada (se puede actualizar después)
        $stationMedia->length = 0;
        
        // Marcar que tiene art (thumbnail de YouTube)
        $stationMedia->art_updated_at = time();

        $stationMedia->updateMetaFields();

        $this->em->persist($stationMedia);
        $this->em->flush();

        $this->logger->info(
            'YouTube video added to media library',
            [
                'station_id' => $station->getId(),
                'video_id' => $videoId,
                'title' => $title,
                'artist' => $artist,
            ]
        );

        return $response->withJson(new Status(
            true,
            'Vídeo de YouTube añadido correctamente.'
        ));
    }

    /**
     * Extrae el ID del vídeo de YouTube de una URL.
     */
    private function extractYouTubeVideoId(string $url): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/',
            '/youtube\.com\/v\/([^&\n?#]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
