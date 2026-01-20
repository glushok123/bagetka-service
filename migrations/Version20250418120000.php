<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250418120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add duration hours and receipt PDF to orders';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` ADD duration_hours INT NOT NULL DEFAULT 2, ADD receipt_pdf VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` DROP duration_hours, DROP receipt_pdf');
    }
}
