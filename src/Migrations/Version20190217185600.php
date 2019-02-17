<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190217185600 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE city MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX UNIQ_2D5B023477153098 ON city');
        $this->addSql('ALTER TABLE city DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE city DROP id');
        $this->addSql('ALTER TABLE city ADD PRIMARY KEY (code)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE city DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE city ADD id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2D5B023477153098 ON city (code)');
        $this->addSql('ALTER TABLE city ADD PRIMARY KEY (id)');
    }
}
