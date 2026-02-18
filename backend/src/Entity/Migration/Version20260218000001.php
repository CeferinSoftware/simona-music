<?php

declare(strict_types=1);

namespace App\Entity\Migration;

use Doctrine\DBAL\Schema\Schema;

final class Version20260218000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create advertisements table for the advertising system.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS advertisements (
            id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            media_type VARCHAR(20) NOT NULL DEFAULT \'audio\',
            media_path VARCHAR(500) DEFAULT NULL,
            media_url VARCHAR(500) DEFAULT NULL,
            duration DOUBLE PRECISION NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT \'active\',
            priority INT NOT NULL DEFAULT 5,
            advertiser_name VARCHAR(255) DEFAULT NULL,
            target_categories JSON DEFAULT NULL,
            target_provinces JSON DEFAULT NULL,
            target_cities JSON DEFAULT NULL,
            start_date DATETIME DEFAULT NULL,
            end_date DATETIME DEFAULT NULL,
            max_plays INT NOT NULL DEFAULT 0,
            play_count INT NOT NULL DEFAULT 0,
            play_frequency INT NOT NULL DEFAULT 5,
            time_start VARCHAR(5) DEFAULT NULL,
            time_end VARCHAR(5) DEFAULT NULL,
            active_days JSON DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE INDEX idx_ad_status ON advertisements (status)');
        $this->addSql('CREATE INDEX idx_ad_priority ON advertisements (priority)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS advertisements');
    }
}
