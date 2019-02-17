<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190217202608 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE city ADD country VARCHAR(10) DEFAULT NULL, DROP country_code');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B02345373C966 FOREIGN KEY (country) REFERENCES country (code)');
        $this->addSql('CREATE INDEX IDX_2D5B02345373C966 ON city (country)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B02345373C966');
        $this->addSql('DROP INDEX IDX_2D5B02345373C966 ON city');
        $this->addSql('ALTER TABLE city ADD country_code VARCHAR(10) NOT NULL COLLATE utf8mb4_unicode_ci, DROP country');
    }
}
