<?php

declare(strict_types=1);

namespace App\Entity\Migration;

use Doctrine\DBAL\Schema\Schema;

final class Version20250117000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add video_url field to station_media table for videoclip support and display_mode to station for visualization preference.';
    }

    public function up(Schema $schema): void
    {
        // Add video_url to station_media for storing YouTube/Vimeo URLs
        $this->addSql('ALTER TABLE station_media ADD video_url VARCHAR(500) DEFAULT NULL AFTER art_updated_at');
        
        // Add display_mode to station for choosing between videoclips and waveform
        $this->addSql('ALTER TABLE station ADD display_mode VARCHAR(20) DEFAULT \'waveform\' NOT NULL AFTER enable_requests');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE station DROP display_mode');
        $this->addSql('ALTER TABLE station_media DROP video_url');
    }
}
