<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190610151753 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE abstr_member (id INT AUTO_INCREMENT NOT NULL, job_id INT NOT NULL, telephone VARCHAR(14) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, payment_day_of_month INT DEFAULT NULL, discriminator VARCHAR(255) NOT NULL, INDEX IDX_8A6531DBBE04EA9 (job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_user (id INT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_711BFA15F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contribution (id INT AUTO_INCREMENT NOT NULL, my_user_id INT NOT NULL, value DOUBLE PRECISION NOT NULL, payment_date DATETIME NOT NULL, INDEX IDX_EA351E152D977FB9 (my_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, rate DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_history (id INT NOT NULL, my_user_id INT DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_984EC7DF2D977FB9 (my_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abstr_member ADD CONSTRAINT FK_8A6531DBBE04EA9 FOREIGN KEY (job_id) REFERENCES job (id)');
        $this->addSql('ALTER TABLE member_user ADD CONSTRAINT FK_711BFA15BF396750 FOREIGN KEY (id) REFERENCES abstr_member (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E152D977FB9 FOREIGN KEY (my_user_id) REFERENCES member_user (id)');
        $this->addSql('ALTER TABLE member_history ADD CONSTRAINT FK_984EC7DF2D977FB9 FOREIGN KEY (my_user_id) REFERENCES member_user (id)');
        $this->addSql('ALTER TABLE member_history ADD CONSTRAINT FK_984EC7DFBF396750 FOREIGN KEY (id) REFERENCES abstr_member (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE member_user DROP FOREIGN KEY FK_711BFA15BF396750');
        $this->addSql('ALTER TABLE member_history DROP FOREIGN KEY FK_984EC7DFBF396750');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E152D977FB9');
        $this->addSql('ALTER TABLE member_history DROP FOREIGN KEY FK_984EC7DF2D977FB9');
        $this->addSql('ALTER TABLE abstr_member DROP FOREIGN KEY FK_8A6531DBBE04EA9');
        $this->addSql('DROP TABLE abstr_member');
        $this->addSql('DROP TABLE member_user');
        $this->addSql('DROP TABLE contribution');
        $this->addSql('DROP TABLE job');
        $this->addSql('DROP TABLE member_history');
    }
}
