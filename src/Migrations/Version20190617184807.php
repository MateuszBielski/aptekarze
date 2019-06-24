<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190617184807 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE member_history ADD who_made_change_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE member_history ADD CONSTRAINT FK_984EC7DFDAAD57D8 FOREIGN KEY (who_made_change_id) REFERENCES member_user (id)');
        $this->addSql('CREATE INDEX IDX_984EC7DFDAAD57D8 ON member_history (who_made_change_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE member_history DROP FOREIGN KEY FK_984EC7DFDAAD57D8');
        $this->addSql('DROP INDEX IDX_984EC7DFDAAD57D8 ON member_history');
        $this->addSql('ALTER TABLE member_history DROP who_made_change_id');
    }
}