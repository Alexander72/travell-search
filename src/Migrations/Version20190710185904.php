<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190710185904 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE flight_avg_price_subscribe (id INT AUTO_INCREMENT NOT NULL, origin_id VARCHAR(10) NULL, destination_id VARCHAR(10) NULL, price_drop_percent INT NOT NULL, chat INT NOT NULL, `from` DATETIME DEFAULT NULL, `to` DATETIME DEFAULT NULL, INDEX IDX_C75E23D756A273CC (origin_id), INDEX IDX_C75E23D7816C6140 (destination_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE flight_avg_price_subscribe ADD CONSTRAINT FK_C75E23D756A273CC FOREIGN KEY (origin_id) REFERENCES city (code)');
        $this->addSql('ALTER TABLE flight_avg_price_subscribe ADD CONSTRAINT FK_C75E23D7816C6140 FOREIGN KEY (destination_id) REFERENCES city (code)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE flight_avg_price_subscribe');
    }
}
