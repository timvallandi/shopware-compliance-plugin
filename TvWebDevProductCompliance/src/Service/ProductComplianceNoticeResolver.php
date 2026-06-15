<?php declare(strict_types=1);

namespace TvWebDev\ProductCompliance\Service;

use TvWebDev\ProductCompliance\TvWebDevProductCompliance;
use TvWebDev\ProductCompliance\Struct\ProductComplianceNoticeStruct;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;

class ProductComplianceNoticeResolver
{
    public function resolve(SalesChannelProductEntity $product): ?ProductComplianceNoticeStruct
    {
        $customFields = $this->getProductCustomFields($product);
        $isRequired = $customFields[TvWebDevProductCompliance::CUSTOM_FIELD_REQUIRED] ?? false;

        if (!$this->isEnabled($isRequired)) {
            return null;
        }

        $notice = $customFields[TvWebDevProductCompliance::CUSTOM_FIELD_NOTICE] ?? null;

        if (!\is_string($notice)) {
            return null;
        }

        $notice = trim($notice);

        if ($notice === '') {
            return null;
        }

        return new ProductComplianceNoticeStruct($notice);
    }

    /**
     * @return array<string, mixed>
     */
    private function getProductCustomFields(SalesChannelProductEntity $product): array
    {
        $translatedCustomFields = $product->getTranslated()['customFields'] ?? null;

        if (\is_array($translatedCustomFields)) {
            return $translatedCustomFields;
        }

        return $product->getCustomFields() ?? [];
    }

    private function isEnabled(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }
}
