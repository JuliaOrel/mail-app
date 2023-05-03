<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230413055220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE mailing_image_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE mailing_image (id INT NOT NULL, mailing_id INT NOT NULL, filename_big VARCHAR(255) NOT NULL, filename_middle VARCHAR(255) NOT NULL, filename_small VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_90692EFB3931AB76 ON mailing_image (mailing_id)');
        $this->addSql('ALTER TABLE mailing_image ADD CONSTRAINT FK_90692EFB3931AB76 FOREIGN KEY (mailing_id) REFERENCES mailing (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE mailing_image_id_seq CASCADE');
        $this->addSql('ALTER TABLE mailing_image DROP CONSTRAINT FK_90692EFB3931AB76');
        $this->addSql('DROP TABLE mailing_image');
    }
}
