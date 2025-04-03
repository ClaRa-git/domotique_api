<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403125738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE planning (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(50) NOT NULL, date_start DATETIME NOT NULL, date_end DATETIME NOT NULL, recurrence VARCHAR(50) NOT NULL, vibe_id INT DEFAULT NULL, INDEX IDX_D499BFF64B255BC3 (vibe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE room_planning (room_id INT NOT NULL, planning_id INT NOT NULL, INDEX IDX_747F7F3254177093 (room_id), INDEX IDX_747F7F323D865311 (planning_id), PRIMARY KEY(room_id, planning_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE setting (id INT AUTO_INCREMENT NOT NULL, value INT NOT NULL, feature_id INT DEFAULT NULL, device_id INT DEFAULT NULL, vibe_id INT DEFAULT NULL, INDEX IDX_9F74B89860E4B879 (feature_id), INDEX IDX_9F74B89894A4C7D4 (device_id), INDEX IDX_9F74B8984B255BC3 (vibe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE song_playlist (song_id INT NOT NULL, playlist_id INT NOT NULL, INDEX IDX_7C5E4765A0BDB2F3 (song_id), INDEX IDX_7C5E47656BBD148 (playlist_id), PRIMARY KEY(song_id, playlist_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE vibe (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(50) NOT NULL, criteria_id INT DEFAULT NULL, image_id INT DEFAULT NULL, playlist_id INT DEFAULT NULL, profile_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_42054C01990BEA15 (criteria_id), INDEX IDX_42054C013DA5256D (image_id), INDEX IDX_42054C016BBD148 (playlist_id), INDEX IDX_42054C01CCFA12B8 (profile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planning ADD CONSTRAINT FK_D499BFF64B255BC3 FOREIGN KEY (vibe_id) REFERENCES vibe (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room_planning ADD CONSTRAINT FK_747F7F3254177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room_planning ADD CONSTRAINT FK_747F7F323D865311 FOREIGN KEY (planning_id) REFERENCES planning (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE setting ADD CONSTRAINT FK_9F74B89860E4B879 FOREIGN KEY (feature_id) REFERENCES feature (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE setting ADD CONSTRAINT FK_9F74B89894A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE setting ADD CONSTRAINT FK_9F74B8984B255BC3 FOREIGN KEY (vibe_id) REFERENCES vibe (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE song_playlist ADD CONSTRAINT FK_7C5E4765A0BDB2F3 FOREIGN KEY (song_id) REFERENCES song (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE song_playlist ADD CONSTRAINT FK_7C5E47656BBD148 FOREIGN KEY (playlist_id) REFERENCES playlist (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vibe ADD CONSTRAINT FK_42054C01990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vibe ADD CONSTRAINT FK_42054C013DA5256D FOREIGN KEY (image_id) REFERENCES image (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vibe ADD CONSTRAINT FK_42054C016BBD148 FOREIGN KEY (playlist_id) REFERENCES playlist (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vibe ADD CONSTRAINT FK_42054C01CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF64B255BC3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room_planning DROP FOREIGN KEY FK_747F7F3254177093
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room_planning DROP FOREIGN KEY FK_747F7F323D865311
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE setting DROP FOREIGN KEY FK_9F74B89860E4B879
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE setting DROP FOREIGN KEY FK_9F74B89894A4C7D4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE setting DROP FOREIGN KEY FK_9F74B8984B255BC3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE song_playlist DROP FOREIGN KEY FK_7C5E4765A0BDB2F3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE song_playlist DROP FOREIGN KEY FK_7C5E47656BBD148
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vibe DROP FOREIGN KEY FK_42054C01990BEA15
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vibe DROP FOREIGN KEY FK_42054C013DA5256D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vibe DROP FOREIGN KEY FK_42054C016BBD148
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vibe DROP FOREIGN KEY FK_42054C01CCFA12B8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE planning
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE room_planning
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE setting
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE song_playlist
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE vibe
        SQL);
    }
}
