<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503093745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('insert into "user" ("email", "full_name", "id", "is_deleted", "is_verified", "password", "phone", "roles") 
        values 
         (\'root@mailman.veronikalove.com\', \'VV\', nextval(\'user_id_seq\'), NULL, false, \'$2y$13$pfS6uEFgpD4OrA0fM.09S.fFHmmMlvXxEh2q3nCijbV7f2JvNqpfq\', \'+380954326714\', \'[]\')
        ');
        $this->addSql('insert into "user" ("email", "full_name", "id", "is_deleted", "is_verified", "password", "phone", "roles") 
        values 
        (\'admin@mailman.veronikalove.com\', \'VV\', nextval(\'user_id_seq\'), NULL, false, \'$2y$13$pfS6uEFgpD4OrA0fM.09S.fFHmmMlvXxEh2q3nCijbV7f2JvNqpfq\', \'+380954326777\', \'[]\')
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM "user" WHERE "email"=\'root@mailman.veronikalove.com\';');
        $this->addSql('DELETE FROM "user" WHERE "email"=\'admin@mailman.veronikalove.com\';');
    }
}
