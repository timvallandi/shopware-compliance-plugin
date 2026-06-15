<?php declare(strict_types=1);

namespace TvWebDev\ProductCompliance\Subscriber;

use TvWebDev\ProductCompliance\TvWebDevProductCompliance;
use TvWebDev\ProductCompliance\Service\ProductComplianceNoticeResolver;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductComplianceSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ProductComplianceNoticeResolver $noticeResolver)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.' . ProductEvents::PRODUCT_LOADED_EVENT => ['onSalesChannelProductLoaded', -100],
            'sales_channel.product.partial_loaded' => ['onSalesChannelProductLoaded', -100],
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
        ];
    }

    public function onSalesChannelProductLoaded(SalesChannelEntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $product) {
            if (!$product instanceof SalesChannelProductEntity) {
                continue;
            }

            $this->applyExtension($product);
        }
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $this->applyExtension($event->getPage()->getProduct());
    }

    private function applyExtension(SalesChannelProductEntity $product): void
    {
        $notice = $this->noticeResolver->resolve($product);

        if ($notice === null) {
            return;
        }

        $product->addExtension(TvWebDevProductCompliance::EXTENSION_NAME, $notice);
    }
}
