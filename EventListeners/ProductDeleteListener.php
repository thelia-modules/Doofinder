<?php

namespace Doofinder\EventListeners;

use Doofinder\Service\DoofinderService;
use Doofinder\Shared\Exceptions\ApiException;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Event\Product\ProductDeleteEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Log\Tlog;

class ProductDeleteListener implements EventSubscriberInterface
{
    public function __construct(
        protected RequestStack     $requestStack,
        protected DoofinderService $doofinderService
    ) {
    }


    public function onProductDelete(ProductDeleteEvent $event): void
    {
        $product = $event->getProduct();

        try {
            Tlog::getInstance()->info($this->doofinderService->deleteDoofinderSingleProduct($product));
        } catch (ApiException|PropelException $e) {
            Tlog::getInstance()->error($e->getMessage());
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::PRODUCT_DELETE => ["onProductDelete", 100],
        ];
    }
}
