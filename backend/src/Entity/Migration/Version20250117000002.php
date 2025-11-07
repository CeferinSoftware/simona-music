<?php

declare(strict_types=1);

namespace App\Entity\Migration;

use Doctrine\DBAL\Schema\Schema;

final class Version20250117000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create station_screens table for managing logical screen displays per station.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE station_screens (
            id INT AUTO_INCREMENT NOT NULL,
            station_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            content_type VARCHAR(50) NOT NULL DEFAULT "nowplaying",
            metadata LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)",
            created_at DATETIME(6) NOT NULL,
            updated_at DATETIME(6) NOT NULL,
            INDEX IDX_station_id (station_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE station_screens ADD CONSTRAINT FK_station_screens_station_id 
            FOREIGN KEY (station_id) REFERENCES station (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE station_screens');
    }
}
