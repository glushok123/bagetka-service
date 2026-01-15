<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250418130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add employees and receipt metadata to orders';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE employee (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, office_type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD receipt_employee_id INT DEFAULT NULL, ADD receipt_amount VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993986D68D80E FOREIGN KEY (receipt_employee_id) REFERENCES employee (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_F52993986D68D80E ON `order` (receipt_employee_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993986D68D80E');
        $this->addSql('DROP TABLE employee');
        $this->addSql('DROP INDEX IDX_F52993986D68D80E ON `order`');
        $this->addSql('ALTER TABLE `order` DROP receipt_employee_id, DROP receipt_amount');
    }
}
