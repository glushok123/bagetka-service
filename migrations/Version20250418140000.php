<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250418140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add receipt created timestamp to orders';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` ADD receipt_created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` DROP receipt_created_at');
    }
}
