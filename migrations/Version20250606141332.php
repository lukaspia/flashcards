<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606141332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE word CHANGE basic_word basic_word VARCHAR(255) DEFAULT NULL, CHANGE translation translation VARCHAR(255) DEFAULT NULL, CHANGE example example LONGTEXT DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE word CHANGE basic_word basic_word VARCHAR(255) NOT NULL, CHANGE translation translation VARCHAR(255) NOT NULL, CHANGE example example LONGTEXT NOT NULL, CHANGE image image VARCHAR(255) NOT NULL
        SQL);
    }
}
