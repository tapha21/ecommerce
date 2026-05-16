<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260511120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize order payment methods for PaymentMethod enum values.';
    }

    public function up(Schema $schema): void
    {
        // Normalisation des anciennes valeurs
        $this->addSql("
            UPDATE orders
            SET payment_method = 'cash_on_delivery'
            WHERE payment_method IS NULL
               OR payment_method = ''
               OR payment_method = 'cash'
        ");

        $this->addSql("
            UPDATE orders
            SET payment_method = 'orange_money'
            WHERE payment_method IN ('orange', 'orange money')
        ");

        $this->addSql("
            UPDATE orders
            SET payment_method = 'card'
            WHERE payment_method IN ('carte', 'carte bancaire', 'credit_card')
        ");

        // Modification de la colonne (PostgreSQL)
        $this->addSql("
            ALTER TABLE orders
            ALTER COLUMN payment_method TYPE VARCHAR(50)
        ");

        $this->addSql("
            ALTER TABLE orders
            ALTER COLUMN payment_method SET NOT NULL
        ");
    }

    public function down(Schema $schema): void
    {
        // Retour aux anciennes valeurs
        $this->addSql("
            UPDATE orders
            SET payment_method = 'cash'
            WHERE payment_method = 'cash_on_delivery'
        ");

        $this->addSql("
            UPDATE orders
            SET payment_method = 'orange'
            WHERE payment_method = 'orange_money'
        ");

        // Rendre la colonne nullable (PostgreSQL)
        $this->addSql("
            ALTER TABLE orders
            ALTER COLUMN payment_method TYPE VARCHAR(50)
        ");

        $this->addSql("
            ALTER TABLE orders
            ALTER COLUMN payment_method DROP NOT NULL
        ");
    }
}