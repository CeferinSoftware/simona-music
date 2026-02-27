<?php

declare(strict_types=1);

namespace App\Radio\AutoDJ;

use App\Event\Radio\AnnotateNextSong;
use App\Entity\StationQueue;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Annotates advertisement queue entries with proper metadata for Liquidsoap.
 * 
 * When a queue entry is an ad (detected by song_id starting with 'AD:' and
 * having autodj_custom_uri but no media), this subscriber adds the necessary
 * annotations so Liquidsoap can play it correctly.
 */
final class AdAnnotations implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AnnotateNextSong::class => [
                ['annotateAdvertisement', 14], // After path (20), before liquidsoap (15)
            ],
        ];
    }

    /**
     * Add annotations for advertisement queue entries.
     */
    public function annotateAdvertisement(AnnotateNextSong $event): void
    {
        $queue = $event->getQueue();
        
        if (!$queue instanceof StationQueue) {
            return;
        }
        
        // Detect if this is an ad entry (no media, has custom URI, title starts with "AD:")
        if ($queue->media !== null) {
            return;
        }
        
        $customUri = $queue->autodj_custom_uri;
        if (empty($customUri)) {
            return;
        }
        
        // Check if this is an advertisement (title starts with "AD:")
        $title = $queue->title ?? '';
        if (!str_starts_with($title, 'AD: ')) {
            return;
        }
        
        $adName = substr($title, 4); // Remove "AD: " prefix
        
        // Add annotations for the ad
        $event->addAnnotations([
            'title' => $adName,
            'artist' => 'Anuncio',
            'duration' => $queue->duration ?? 30.0,
            'jingle_mode' => 'true', // Treat ads like jingles (no crossfade)
            'sq_id' => $queue->id,
        ]);
    }
}
