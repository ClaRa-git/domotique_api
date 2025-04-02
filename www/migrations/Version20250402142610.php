<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402142610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE criteria (id INT AUTO_INCREMENT NOT NULL, mood INT NOT NULL, stress INT NOT NULL, tone INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE device (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(50) NOT NULL, address VARCHAR(50) NOT NULL, brand VARCHAR(50) NOT NULL, reference VARCHAR(50) NOT NULL, state TINYINT(1) NOT NULL, device_type_id INT DEFAULT NULL, room_id INT DEFAULT NULL, INDEX IDX_92FB68E4FFA550E (device_type_id), INDEX IDX_92FB68E54177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE device_type (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE feature (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(50) NOT NULL, unit_id INT DEFAULT NULL, INDEX IDX_1FD77566F8BD700D (unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE playlist (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(50) NOT NULL, image_id INT DEFAULT NULL, profile_id INT DEFAULT NULL, INDEX IDX_D782112D3DA5256D (image_id), INDEX IDX_D782112DCCFA12B8 (profile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(50) NOT NULL, image_id INT DEFAULT NULL, INDEX IDX_729F519B3DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE song (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(50) NOT NULL, artist VARCHAR(50) NOT NULL, duration INT NOT NULL, file_path VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE unit (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE device ADD CONSTRAINT FK_92FB68E4FFA550E FOREIGN KEY (device_type_id) REFERENCES device_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE device ADD CONSTRAINT FK_92FB68E54177093 FOREIGN KEY (room_id) REFERENCES room (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feature ADD CONSTRAINT FK_1FD77566F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE playlist ADD CONSTRAINT FK_D782112D3DA5256D FOREIGN KEY (image_id) REFERENCES image (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE playlist ADD CONSTRAINT FK_D782112DCCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room ADD CONSTRAINT FK_729F519B3DA5256D FOREIGN KEY (image_id) REFERENCES image (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE device DROP FOREIGN KEY FK_92FB68E4FFA550E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE device DROP FOREIGN KEY FK_92FB68E54177093
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feature DROP FOREIGN KEY FK_1FD77566F8BD700D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE playlist DROP FOREIGN KEY FK_D782112D3DA5256D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE playlist DROP FOREIGN KEY FK_D782112DCCFA12B8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room DROP FOREIGN KEY FK_729F519B3DA5256D
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE criteria
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE device
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE device_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE feature
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE playlist
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE room
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE song
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE unit
        SQL);
    }
}
