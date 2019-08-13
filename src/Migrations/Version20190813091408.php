<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190813091408 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job ADD replaced_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F89AC69B54 FOREIGN KEY (replaced_by_id) REFERENCES job (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FBD8E0F89AC69B54 ON job (replaced_by_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F89AC69B54');
        $this->addSql('DROP INDEX UNIQ_FBD8E0F89AC69B54 ON job');
        $this->addSql('ALTER TABLE job DROP replaced_by_id');
    }
}
