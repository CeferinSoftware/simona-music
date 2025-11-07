<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin\BatchActions;

use App\Controller\SingleActionInterface;
use App\Entity\Repository\StationPlaylistMediaRepository;
use App\Entity\Repository\StationPlaylistRepository;
use App\Entity\Repository\StationMediaRepository;
use App\Entity\Repository\StationRepository;
use App\Entity\StationPlaylist;
use App\Entity\StationMedia;
use App\Http\Response;
use App\Http\ServerRequest;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;

final class AddMediaToPlaylistsAction implements SingleActionInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly StationRepository $stationRepo,
        private readonly StationMediaRepository $mediaRepo,
        private readonly StationPlaylistRepository $playlistRepo,
        private readonly StationPlaylistMediaRepository $spmRepo,
    ) {
    }

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $body = $request->getParsedBody();
        
        $operations = $body['operations'] ?? [];
        
        if (empty($operations)) {
            return $response->withJson([
                'success' => false,
                'message' => 'No operations provided'
            ], 400);
        }

        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($operations as $operation) {
            $stationId = (int)($operation['station_id'] ?? 0);
            $playlistId = (int)($operation['playlist_id'] ?? 0);
            $mediaId = (int)($operation['media_id'] ?? 0);

            try {
                // Verify station exists
                $station = $this->stationRepo->find($stationId);
                if (!$station) {
                    throw new \RuntimeException("Station {$stationId} not found");
                }

                // Verify playlist exists and belongs to station
                $playlist = $this->playlistRepo->find($playlistId);
                if (!$playlist || $playlist->getStation()->getId() !== $stationId) {
                    throw new \RuntimeException("Playlist {$playlistId} not found or doesn't belong to station");
                }

                // Verify media exists and belongs to station
                $media = $this->mediaRepo->find($mediaId);
                if (!$media || $media->getStorage()->getStation()->getId() !== $stationId) {
                    throw new \RuntimeException("Media {$mediaId} not found or doesn't belong to station");
                }

                // Add media to playlist
                $this->spmRepo->addMediaToPlaylist($media, $playlist);
                $this->em->flush();

                $results[] = [
                    'station_id' => $stationId,
                    'playlist_id' => $playlistId,
                    'media_id' => $mediaId,
                    'success' => true,
                    'message' => 'Media added successfully'
                ];
                $successCount++;
            } catch (\Exception $e) {
                $results[] = [
                    'station_id' => $stationId,
                    'playlist_id' => $playlistId,
                    'media_id' => $mediaId,
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                $errorCount++;
            }
        }

        return $response->withJson([
            'success' => $errorCount === 0,
            'total' => count($operations),
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'results' => $results
        ]);
    }
}
