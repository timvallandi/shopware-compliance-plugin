<?php declare(strict_types=1);

namespace TvWebDev\ProductCompliance\Struct;

use Shopware\Core\Framework\Struct\Struct;

class ProductComplianceNoticeStruct extends Struct
{
    public function __construct(private readonly string $notice)
    {
    }

    public function getNotice(): string
    {
        return $this->notice;
    }

    public function getApiAlias(): string
    {
        return 'tvwebdev_product_compliance_notice';
    }
}
