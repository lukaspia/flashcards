<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250618110737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE word_category (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE word ADD word_category INT DEFAULT NULL, ADD errors INT NOT NULL, ADD color VARCHAR(7) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE word ADD CONSTRAINT FK_C3F1751122F2C810 FOREIGN KEY (word_category) REFERENCES word_category (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C3F1751122F2C810 ON word (word_category)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE word DROP FOREIGN KEY FK_C3F1751122F2C810
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE word_category
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C3F1751122F2C810 ON word
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE word DROP word_category, DROP errors, DROP color
        SQL);
    }
}
