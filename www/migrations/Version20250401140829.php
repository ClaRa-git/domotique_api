<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401140829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE avatar (id INT AUTO_INCREMENT NOT NULL, image_path VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE profile (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, avatar_id INT DEFAULT NULL, INDEX IDX_8157AA0F86383B10 (avatar_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F86383B10 FOREIGN KEY (avatar_id) REFERENCES avatar (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0F86383B10
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE avatar
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE profile
        SQL);
    }
}
