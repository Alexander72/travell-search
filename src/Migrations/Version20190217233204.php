<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Country;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190217233204 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE country ADD continent VARCHAR(100) NOT NULL');

        $europe = Country::CONTINENT_EUROPE;
        $this->addSql("UPDATE country SET continent = '$europe' WHERE `name` IN('Сан Марино', 'Австрия','Азербайджан','Албания','Андорра','Армения','Беларусь','Бельгия','Болгария','Босния и Герцеговина','Ватикан','Великобритания','Венгрия','Германия','Греция','Грузия','Дания','Ирландия','Исландия','Испания','Италия','Казахстан','Кипр','Латвия','Литва','Лихтенштейн','Люксембург','Мальта','Молдова','Монако','Нидерланды','Норвегия','Польша','Португалия','Македония','Россия','Румыния','СанМарино','Сербия','Словакия','Словения','Турция','Украина','Финляндия','Франция','Хорватия','Черногория','Чехия','Швейцария','Швеция','Эстония')");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE country DROP continent');
    }
}
