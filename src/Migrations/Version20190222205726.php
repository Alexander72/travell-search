<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190222205726 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE route (id INT AUTO_INCREMENT NOT NULL, origin_id VARCHAR(10) NOT NULL, destination_id VARCHAR(10) NOT NULL, INDEX IDX_2C4207956A273CC (origin_id), INDEX IDX_2C42079816C6140 (destination_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE search_request (id INT AUTO_INCREMENT NOT NULL, date_from DATETIME DEFAULT NULL, date_to DATETIME DEFAULT NULL, days_duration_min INT DEFAULT NULL, days_duration_max INT DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE route ADD CONSTRAINT FK_2C4207956A273CC FOREIGN KEY (origin_id) REFERENCES city (code)');
        $this->addSql('ALTER TABLE route ADD CONSTRAINT FK_2C42079816C6140 FOREIGN KEY (destination_id) REFERENCES city (code)');
        $this->addSql('ALTER TABLE country CHANGE continent continent VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE route');
        $this->addSql('DROP TABLE search_request');
        $this->addSql('ALTER TABLE country CHANGE continent continent VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
