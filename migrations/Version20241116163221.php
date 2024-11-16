<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241116163221 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE stat (
                id INT AUTO_INCREMENT NOT NULL,
                last_hit DATETIME NOT NULL,
                hits_count INT NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('INSERT INTO stat (last_hit, hits_count) VALUES (NOW(), 0)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE stat');
    }
}
