<?php

declare(strict_types=1);

namespace App\Entity\Migration;

use Doctrine\DBAL\Schema\Schema;

final class Version20250117000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add requester identity and comment fields to station_requests table for QR request tracking.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE station_requests ADD requester_name VARCHAR(100) DEFAULT NULL AFTER ip');
        $this->addSql('ALTER TABLE station_requests ADD requester_avatar VARCHAR(500) DEFAULT NULL AFTER requester_name');
        $this->addSql('ALTER TABLE station_requests ADD comment LONGTEXT DEFAULT NULL AFTER requester_avatar');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE station_requests DROP comment');
        $this->addSql('ALTER TABLE station_requests DROP requester_avatar');
        $this->addSql('ALTER TABLE station_requests DROP requester_name');
    }
}
