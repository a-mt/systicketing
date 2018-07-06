<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180706114034 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, archive TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_user (project_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B4021E51166D1F9C (project_id), INDEX IDX_B4021E51A76ED395 (user_id), PRIMARY KEY(project_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, assigned_to_id INT DEFAULT NULL, created_by_id INT NOT NULL, title VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, internal TINYINT(1) NOT NULL, description LONGTEXT NOT NULL, type SMALLINT NOT NULL, urgency SMALLINT NOT NULL, status SMALLINT NOT NULL, INDEX IDX_527EDB25166D1F9C (project_id), INDEX IDX_527EDB25F4BD7827 (assigned_to_id), INDEX IDX_527EDB25B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_history (id INT AUTO_INCREMENT NOT NULL, assigned_to_id INT DEFAULT NULL, task_id INT NOT NULL, updated_by_id INT NOT NULL, type SMALLINT NOT NULL, urgency SMALLINT NOT NULL, status SMALLINT NOT NULL, date DATETIME NOT NULL, INDEX IDX_385B5AA1F4BD7827 (assigned_to_id), INDEX IDX_385B5AA18DB60186 (task_id), INDEX IDX_385B5AA1896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(254) NOT NULL, first_name LONGTEXT NOT NULL, last_name LONGTEXT NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, status SMALLINT NOT NULL, UNIQUE INDEX UNIQ_C2502824E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_file (id INT AUTO_INCREMENT NOT NULL, task_id INT NOT NULL, added_by_id INT NOT NULL, drive_id VARCHAR(255) NOT NULL, date DATETIME NOT NULL, name VARCHAR(255) NOT NULL, filesize VARCHAR(255) NOT NULL, INDEX IDX_FF2CA26B8DB60186 (task_id), INDEX IDX_FF2CA26B55B127A4 (added_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_discuss (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, task_id INT NOT NULL, text LONGTEXT NOT NULL, internal TINYINT(1) NOT NULL, date DATETIME NOT NULL, INDEX IDX_DA4AA10AB03A8386 (created_by_id), INDEX IDX_DA4AA10A8DB60186 (task_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project_user ADD CONSTRAINT FK_B4021E51166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_user ADD CONSTRAINT FK_B4021E51A76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES app_users (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25B03A8386 FOREIGN KEY (created_by_id) REFERENCES app_users (id)');
        $this->addSql('ALTER TABLE task_history ADD CONSTRAINT FK_385B5AA1F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES app_users (id)');
        $this->addSql('ALTER TABLE task_history ADD CONSTRAINT FK_385B5AA18DB60186 FOREIGN KEY (task_id) REFERENCES task (id)');
        $this->addSql('ALTER TABLE task_history ADD CONSTRAINT FK_385B5AA1896DBBDE FOREIGN KEY (updated_by_id) REFERENCES app_users (id)');
        $this->addSql('ALTER TABLE task_file ADD CONSTRAINT FK_FF2CA26B8DB60186 FOREIGN KEY (task_id) REFERENCES task (id)');
        $this->addSql('ALTER TABLE task_file ADD CONSTRAINT FK_FF2CA26B55B127A4 FOREIGN KEY (added_by_id) REFERENCES app_users (id)');
        $this->addSql('ALTER TABLE task_discuss ADD CONSTRAINT FK_DA4AA10AB03A8386 FOREIGN KEY (created_by_id) REFERENCES app_users (id)');
        $this->addSql('ALTER TABLE task_discuss ADD CONSTRAINT FK_DA4AA10A8DB60186 FOREIGN KEY (task_id) REFERENCES task (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE project_user DROP FOREIGN KEY FK_B4021E51166D1F9C');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25166D1F9C');
        $this->addSql('ALTER TABLE task_history DROP FOREIGN KEY FK_385B5AA18DB60186');
        $this->addSql('ALTER TABLE task_file DROP FOREIGN KEY FK_FF2CA26B8DB60186');
        $this->addSql('ALTER TABLE task_discuss DROP FOREIGN KEY FK_DA4AA10A8DB60186');
        $this->addSql('ALTER TABLE project_user DROP FOREIGN KEY FK_B4021E51A76ED395');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25F4BD7827');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25B03A8386');
        $this->addSql('ALTER TABLE task_history DROP FOREIGN KEY FK_385B5AA1F4BD7827');
        $this->addSql('ALTER TABLE task_history DROP FOREIGN KEY FK_385B5AA1896DBBDE');
        $this->addSql('ALTER TABLE task_file DROP FOREIGN KEY FK_FF2CA26B55B127A4');
        $this->addSql('ALTER TABLE task_discuss DROP FOREIGN KEY FK_DA4AA10AB03A8386');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE project_user');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE task_history');
        $this->addSql('DROP TABLE app_users');
        $this->addSql('DROP TABLE task_file');
        $this->addSql('DROP TABLE task_discuss');
    }
}
