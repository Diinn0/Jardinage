<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210618100501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C79BF1BCE');
        $this->addSql('DROP INDEX IDX_9474526C79BF1BCE ON comment');
        $this->addSql('ALTER TABLE comment DROP answers_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment ADD answers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C79BF1BCE FOREIGN KEY (answers_id) REFERENCES comment (id)');
        $this->addSql('CREATE INDEX IDX_9474526C79BF1BCE ON comment (answers_id)');
    }
}
