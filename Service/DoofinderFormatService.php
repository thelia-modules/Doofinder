<?php

namespace Doofinder\Service;

use Doofinder\Doofinder;
use Propel\Runtime\Exception\PropelException;
use Thelia\Log\Tlog;
use Thelia\Model\Country;
use Thelia\Model\LangQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\TaxRule;
use Thelia\Domain\Taxation\TaxEngine\Calculator;
use Thelia\Domain\Taxation\TaxEngine\TaxEngine;
use Thelia\Tools\URL;

class DoofinderFormatService
{
    public function __construct(
        protected TaxEngine $taxEngine
    )
    {
    }


    /**
     * @throws PropelException
     */
    public function formatIndexImport(ProductSaleElements $productSaleElements): array
    {
        $categories = [];
        $product = $productSaleElements->getProduct();
        $locale = $this->getLocale();

        foreach ($product->getCategories() as $category) {
            $categories[] = $category->setLocale($locale)->getTitle();
        }

        return [
            'availability' => $this->getAvailability($productSaleElements->getQuantity()),
            'brand' => (string)$product->getBrand()?->setLocale($locale)->getTitle(),
            'categories' => $categories,
            'description' => $product->getDescription(),
            'group_id' => (string)$product->getId(),
            'gtin' => $productSaleElements->getEanCode(),
            'id' => (string)$productSaleElements->getId(),
            'image_link' => $this->getImageLink($product),
            'link' => $this->getProductLink($product, $locale),
            //'mpn' => '', //référence fabricant ?
            'best_price' => (string)$this->getPseProductPrice($productSaleElements->getId(), $product->getTaxRule(), true),
            'sale_price' => (string)$this->getPseProductPrice($productSaleElements->getId(), $product->getTaxRule()),
            'title' => $product->setLocale($locale)->getTitle(),
        ];
    }

    public function formatResponse(array $results): string
    {
        $output = "";

        if (isset($results[Doofinder::DOOFINDER_STATE_CREATED_UPDATED])) {
            $output .= $this->formatAddedUpdatedResponse($results[Doofinder::DOOFINDER_STATE_CREATED_UPDATED]);
        }

        if (isset($results[Doofinder::DOOFINDER_STATE_DELETED])) {
            $output .= $this->formatDeletedResponse($results[Doofinder::DOOFINDER_STATE_DELETED]);
        }

        return $output;
    }

    public function formatAddedUpdatedResponse(array $results): string
    {
        $productsAdded = 0;
        $productsUpdated = 0;

        foreach ($results['results'] as $product) {
            if ($product["result"] === Doofinder::DOOFINDER_STATE_CREATED) {
                $productsAdded++;
            }
            if ($product["result"] === Doofinder::DOOFINDER_STATE_UPDATED) {
                $productsUpdated++;
            }
        }

        $output = sprintf("product created : %s, product updated : %s\n", $productsAdded, $productsUpdated);
        Tlog::getInstance()->info($output);

        return $output;
    }

    public function formatDeletedResponse(array $results): string
    {
        $productsDeleted = 0;

        foreach ($results['results'] as $product) {
            if ($product["result"] === Doofinder::DOOFINDER_STATE_DELETED) {
                $productsDeleted++;
            }
        }

        $output = sprintf("product deleted : %s \n", $productsDeleted);
        Tlog::getInstance()->info($output);

        return $output;
    }

    /**
     * @throws PropelException
     */
    private function getImageLink(Product $product): string
    {
        $url = THELIA_LOCAL_DIR . 'media' . DS . 'product' . DS;
        return URL::getInstance()->absoluteUrl($url . $product->getProductImages()->getFirst()?->getFile());
    }
    private function getAvailability(float $quantity): string
    {
        if ($quantity > 0) {
            return "in stock";
        }

        return "out of stock";
    }
    /**
     * @throws PropelException
     */
    private function getPseProductPrice(int $pseId, TaxRule $taxRule, bool $isPromo = false): float|int
    {
        $calculator = new Calculator();

        $calculator->loadTaxRuleWithoutProduct(
            $taxRule,
            Country::getShopLocation()
        );

        $productPrice = ProductPriceQuery::create()->findOneByProductSaleElementsId($pseId);

        $price = $productPrice?->getPrice();
        if ($isPromo) {
            $price = $productPrice?->getPromoPrice();
        }

        return $calculator->getTaxedPrice($price);
    }
    private function getProductLink(Product $product, string $locale): ?string
    {
        return URL::getInstance()->absoluteUrl($product->getRewrittenUrl($locale));
    }
    private function getLocale(): ?string
    {
        return LangQuery::create()
            ->findOneByByDefault(1)
            ?->getLocale()
        ;
    }
}