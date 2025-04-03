<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403134652 extends AbstractMigration
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
            CREATE TABLE icon (id INT AUTO_INCREMENT NOT NULL, image_path VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, image_path VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE playlist ADD image_id INT DEFAULT NULL, DROP image_path
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE playlist ADD CONSTRAINT FK_D782112D3DA5256D FOREIGN KEY (image_id) REFERENCES image (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D782112D3DA5256D ON playlist (image_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE profile ADD avatar_id INT DEFAULT NULL, DROP avatar_path
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F86383B10 FOREIGN KEY (avatar_id) REFERENCES avatar (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8157AA0F86383B10 ON profile (avatar_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room ADD image_id INT DEFAULT NULL, DROP image_path
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room ADD CONSTRAINT FK_729F519B3DA5256D FOREIGN KEY (image_id) REFERENCES image (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_729F519B3DA5256D ON room (image_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vibe ADD icon_id INT DEFAULT NULL, DROP icon_path
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vibe ADD CONSTRAINT FK_42054C0154B9D732 FOREIGN KEY (icon_id) REFERENCES icon (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42054C0154B9D732 ON vibe (icon_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE avatar
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE icon
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE image
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room DROP FOREIGN KEY FK_729F519B3DA5256D
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_729F519B3DA5256D ON room
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room ADD image_path VARCHAR(255) NOT NULL, DROP image_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vibe DROP FOREIGN KEY FK_42054C0154B9D732
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_42054C0154B9D732 ON vibe
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vibe ADD icon_path VARCHAR(255) NOT NULL, DROP icon_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE playlist DROP FOREIGN KEY FK_D782112D3DA5256D
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D782112D3DA5256D ON playlist
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE playlist ADD image_path VARCHAR(255) NOT NULL, DROP image_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0F86383B10
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8157AA0F86383B10 ON profile
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE profile ADD avatar_path VARCHAR(255) NOT NULL, DROP avatar_id
        SQL);
    }
}
