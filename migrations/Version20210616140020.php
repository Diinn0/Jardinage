<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210616140020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum_category ADD parent_category_id INT DEFAULT NULL, CHANGE imgage image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE forum_category ADD CONSTRAINT FK_21BF9426796A8F92 FOREIGN KEY (parent_category_id) REFERENCES forum_category (id)');
        $this->addSql('CREATE INDEX IDX_21BF9426796A8F92 ON forum_category (parent_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum_category DROP FOREIGN KEY FK_21BF9426796A8F92');
        $this->addSql('DROP INDEX IDX_21BF9426796A8F92 ON forum_category');
        $this->addSql('ALTER TABLE forum_category DROP parent_category_id, CHANGE image imgage VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
