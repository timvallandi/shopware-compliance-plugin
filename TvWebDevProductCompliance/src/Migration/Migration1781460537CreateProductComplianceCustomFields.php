<?php declare(strict_types=1);

namespace TvWebDev\ProductCompliance\Migration;

use TvWebDev\ProductCompliance\TvWebDevProductCompliance;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1781460537CreateProductComplianceCustomFields extends MigrationStep
{
    private const FIELD_SET_ID = 'a1f60d3b9fd34a77a9427060730d9b01';
    private const FIELD_SET_RELATION_ID = 'a1f60d3b9fd34a77a9427060730d9b02';
    private const REQUIRED_FIELD_ID = 'a1f60d3b9fd34a77a9427060730d9b03';
    private const NOTICE_FIELD_ID = 'a1f60d3b9fd34a77a9427060730d9b04';

    public function getCreationTimestamp(): int
    {
        return 1781460537;
    }

    public function update(Connection $connection): void
    {
        $this->createCustomFieldSet($connection);
        $this->createCustomFieldSetRelation($connection);
        $this->createCustomFields($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function createCustomFieldSet(Connection $connection): void
    {
        $connection->executeStatement(
            <<<'SQL'
            INSERT INTO `custom_field_set` (`id`, `name`, `config`, `active`, `app_id`, `position`, `global`, `created_at`, `updated_at`)
            VALUES (
                UNHEX(:id),
                :name,
                :config,
                1,
                NULL,
                0,
                0,
                NOW(3),
                NULL
            )
            ON DUPLICATE KEY UPDATE
                `config` = VALUES(`config`),
                `active` = VALUES(`active`),
                `updated_at` = NOW(3)
            SQL,
            [
                'id' => self::FIELD_SET_ID,
                'name' => TvWebDevProductCompliance::CUSTOM_FIELD_SET_NAME,
                'config' => json_encode([
                    'label' => [
                        'de-DE' => 'Produkt- & Compliance-Hinweis',
                        'en-GB' => 'Product & compliance notice',
                    ],
                    'translated' => true,
                ], \JSON_THROW_ON_ERROR),
            ]
        );
    }

    private function createCustomFieldSetRelation(Connection $connection): void
    {
        $connection->executeStatement(
            <<<'SQL'
            INSERT IGNORE INTO `custom_field_set_relation` (`id`, `set_id`, `entity_name`, `created_at`, `updated_at`)
            VALUES (UNHEX(:id), UNHEX(:setId), 'product', NOW(3), NULL)
            SQL,
            [
                'id' => self::FIELD_SET_RELATION_ID,
                'setId' => self::FIELD_SET_ID,
            ]
        );
    }

    private function createCustomFields(Connection $connection): void
    {
        $connection->executeStatement(
            <<<'SQL'
            INSERT INTO `custom_field` (`id`, `name`, `type`, `config`, `active`, `set_id`, `created_at`, `updated_at`)
            VALUES
                (UNHEX(:requiredFieldId), :requiredName, 'bool', :requiredConfig, 1, UNHEX(:setId), NOW(3), NULL),
                (UNHEX(:noticeFieldId), :noticeName, 'text', :noticeConfig, 1, UNHEX(:setId), NOW(3), NULL)
            ON DUPLICATE KEY UPDATE
                `type` = VALUES(`type`),
                `config` = VALUES(`config`),
                `active` = VALUES(`active`),
                `set_id` = VALUES(`set_id`),
                `updated_at` = NOW(3)
            SQL,
            [
                'requiredFieldId' => self::REQUIRED_FIELD_ID,
                'noticeFieldId' => self::NOTICE_FIELD_ID,
                'setId' => self::FIELD_SET_ID,
                'requiredName' => TvWebDevProductCompliance::CUSTOM_FIELD_REQUIRED,
                'noticeName' => TvWebDevProductCompliance::CUSTOM_FIELD_NOTICE,
                'requiredConfig' => json_encode([
                    'label' => [
                        'de-DE' => 'Besonderer Hinweis erforderlich',
                        'en-GB' => 'Special notice required',
                    ],
                    'helpText' => [
                        'de-DE' => 'Aktiviert die Ausgabe des gepflegten Hinweises auf der Produktdetailseite.',
                        'en-GB' => 'Enables the configured notice on the product detail page.',
                    ],
                    'componentName' => 'sw-field',
                    'type' => 'checkbox',
                    'customFieldType' => 'checkbox',
                    'customFieldPosition' => 1,
                ], \JSON_THROW_ON_ERROR),
                'noticeConfig' => json_encode([
                    'label' => [
                        'de-DE' => 'Hinweistext',
                        'en-GB' => 'Notice text',
                    ],
                    'helpText' => [
                        'de-DE' => 'Wird nur angezeigt, wenn der besondere Hinweis aktiv ist.',
                        'en-GB' => 'Displayed only when the special notice is active.',
                    ],
                    'placeholder' => [
                        'de-DE' => 'Rechtlichen oder anwendungsbezogenen Produkthinweis eingeben...',
                        'en-GB' => 'Enter legal or usage-related product notice...',
                    ],
                    'componentName' => 'sw-textarea-field',
                    'type' => 'textarea',
                    'customFieldType' => 'text',
                    'customFieldPosition' => 2,
                ], \JSON_THROW_ON_ERROR),
            ]
        );
    }
}
