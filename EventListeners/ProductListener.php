<?php

namespace Doofinder\EventListeners;

use Doofinder\Doofinder;
use Doofinder\Service\DoofinderService;
use Doofinder\Shared\Exceptions\ApiException;
use Exception;
use Propel\Runtime\Event\ActiveRecordEvent;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Log\Tlog;
use Thelia\Model\Event\BrandEvent;
use Thelia\Model\Event\BrandI18nEvent;
use Thelia\Model\Event\CategoryEvent;
use Thelia\Model\Event\CategoryI18nEvent;
use Thelia\Model\Event\FeatureProductEvent;
use Thelia\Model\Event\ProductCategoryEvent;
use Thelia\Model\Event\ProductEvent;
use Thelia\Model\Event\ProductI18nEvent;
use Thelia\Model\Event\ProductImageEvent;
use Thelia\Model\Event\ProductPriceEvent;
use Thelia\Model\Event\ProductSaleElementsEvent;

class ProductListener implements EventSubscriberInterface
{
    public function __construct(
        protected RequestStack     $requestStack,
        protected DoofinderService $doofinderService
    ) {
    }

    public function onModelSave(ActiveRecordEvent $event): void
    {
        try {
            Tlog::getInstance()->info($this->doofinderService->synchronizeDoofinderProducts());
        } catch (ApiException|PropelException $e) {
            Tlog::getInstance()->error($e->getMessage());
        }
    }

    public function onObjectRelatedProductSave(ActiveRecordEvent $event): void
    {
        $model = $event->getModel();

        try {
            Tlog::getInstance()->info($this->doofinderService->synchronizeDoofinderProducts($model->getProduct()->getId()));
        } catch (ApiException|PropelException|Exception $e) {
            Tlog::getInstance()->error($e->getMessage());
        }
    }

    public function onProductSave(ProductEvent $event): void
    {
        $product = $event->getModel();

        try {
            Tlog::getInstance()->info($this->doofinderService->synchronizeDoofinderProducts($product->getId()));
        } catch (ApiException|PropelException $e) {
            Tlog::getInstance()->error($e->getMessage());
        }
    }

    public function onProductPriceSave(ProductPriceEvent $event): void
    {
        $productPrice = $event->getModel();

        try {
            Tlog::getInstance()->info($this->doofinderService->synchronizeDoofinderProducts($productPrice->getProductSaleElements()->getProductId()));
        } catch (ApiException|PropelException $e) {
            Tlog::getInstance()->error($e->getMessage());
        }
    }

    public static function getSubscribedEvents(): array
    {
        if (!Doofinder::getConfigValue(Doofinder::DOOFINDER_REAL_TIME_SYNC_CONFIG_KEY)) {
            return [];
        }

        return array(
            ProductEvent::POST_UPDATE => ["onProductSave", 200],

            ProductEvent::POST_SAVE => ["onProductSave", 200],
            ProductI18nEvent::POST_SAVE => ['onObjectRelatedProductSave', 200],
            ProductPriceEvent::POST_UPDATE  => ['onProductPriceSave', 200],
            ProductSaleElementsEvent::POST_SAVE  => ['onObjectRelatedProductSave', 200],
            ProductImageEvent::POST_SAVE => ['onObjectRelatedProductSave', 200],
            FeatureProductEvent::POST_SAVE => ['onObjectRelatedProductSave', 200],
            ProductCategoryEvent::POST_SAVE  => ['onObjectRelatedProductSave', 200],
            BrandEvent::POST_SAVE => ['onModelSave', 200],
            BrandI18nEvent::POST_SAVE => ['onModelSave', 200],
            CategoryEvent::POST_SAVE => ['onModelSave', 200],
            CategoryI18nEvent::POST_SAVE => ['onModelSave', 200],
            /*
            FeatureEvent::POST_SAVE => ['onModelSave', 200],
            FeatureI18nEvent::POST_SAVE => ['onModelSave', 200],
            FeatureAvEvent::POST_SAVE => ['onModelSave', 200],
            FeatureAvI18nEvent::POST_SAVE => ['onModelSave', 200],
            AttributeEvent::POST_SAVE => ['onModelSave', 200],
            AttributeI18nEvent::POST_SAVE => ['onModelSave', 200],
            AttributeAvEvent::POST_SAVE => ['onModelSave', 200],
            AttributeAvI18nEvent::POST_SAVE => ['onModelSave', 200],
            AttributeCombinationEvent::POST_SAVE => ['onModelSave', 200],
            */

            ProductEvent::POST_DELETE => ["onProductSave", 200],
            ProductPriceEvent::POST_DELETE  => ['onProductPriceSave', 200],
            ProductSaleElementsEvent::POST_DELETE  => ['onObjectRelatedProductSave', 200],
            ProductI18nEvent::POST_DELETE => ['onObjectRelatedProductSave', 200],
            ProductImageEvent::POST_DELETE => ['onObjectRelatedProductSave', 200],
            FeatureProductEvent::POST_DELETE => ['onObjectRelatedProductSave', 200],
            ProductCategoryEvent::POST_DELETE  => ['onObjectRelatedProductSave', 200],
            BrandEvent::POST_DELETE => ['onModelSave', 200],
            BrandI18nEvent::POST_DELETE => ['onModelSave', 200],
            CategoryEvent::POST_DELETE => ['onModelSave', 200],
            CategoryI18nEvent::POST_DELETE => ['onModelSave', 200],
            /*
            FeatureEvent::POST_DELETE => ['onModelSave', 200],
            FeatureI18nEvent::POST_DELETE => ['onModelSave', 200],
            FeatureAvEvent::POST_DELETE => ['onModelSave', 200],
            FeatureAvI18nEvent::POST_DELETE => ['onModelSave', 200],
            AttributeEvent::POST_DELETE => ['onModelSave', 200],
            AttributeI18nEvent::POST_DELETE => ['onModelSave', 200],
            AttributeAvEvent::POST_DELETE => ['onModelSave', 200],
            AttributeAvI18nEvent::POST_DELETE => ['onModelSave', 200],
            AttributeCombinationEvent::POST_DELETE => ['onModelSave', 200],
            */
        );
    }
}