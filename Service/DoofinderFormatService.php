<?php

namespace Doofinder\Service;

use Doofinder\Doofinder;
use Propel\Runtime\Exception\PropelException;
use Thelia\Log\Tlog;
use Thelia\Model\AttributeCombination;
use Thelia\Model\Base\FeatureProductQuery;
use Thelia\Model\Country;
use Thelia\Model\FeatureProduct;
use Thelia\Model\LangQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\TaxRule;
use Thelia\TaxEngine\Calculator;
use Thelia\TaxEngine\TaxEngine;
use Thelia\Tools\URL;

class DoofinderFormatService
{
    public function __construct(protected TaxEngine $taxEngine)
    {}

    /**
     * @throws PropelException
     */
    public function formatIndexImport(Product $product): array
    {
        $categories = [];
        $locale = $this->getLocale();

        foreach ($product->getCategories() as $category) {
            $categories[] = $category->setLocale($locale)->getTitle() ?? "";
        }

        $features = [];
        $featureProducts = FeatureProductQuery::create()->findByProductId($product->getId());

        foreach ($featureProducts as $key => $featureProduct) {
            $features[$key]['type'] = $featureProduct->getFeature()->setLocale($locale)->getTitle();
            $features[$key]['value'] = $featureProduct->getFeatureAv()?->setLocale($locale)->getTitle();
        }

        return [
            'availability' => $this->getAvailability($product),
            'brand' => (string)$product->getBrand()?->setLocale($locale)->getTitle(),
            'categories' => $categories,
            'description' => $product->getDescription(),
            //'group_id' => (string)$product->getId(),
            //'gtin' => $productSaleElements->getEanCode(),
            'id' => (string)$product->getId(),
            'image_link' => $this->getImageLink($product),
            'link' => $this->getProductLink($product, $locale),
            //'mpn' => '', //référence fabricant ?
            'best_price' => (string)$this->getProductPrice($product, $product->getTaxRule(), true),
            'sale_price' => (string)$this->getProductPrice($product, $product->getTaxRule()),
            'title' => $product->setLocale($locale)->getTitle(),
            'features' => $features
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

    public function formatAddedUpdatedResponse(array $arrayResults): string
    {
        $productsAdded = 0;
        $productsUpdated = 0;

        foreach ($arrayResults as $results) {
            foreach ($results['results'] as $product) {
                if ($product["result"] === Doofinder::DOOFINDER_STATE_CREATED) {
                    $productsAdded++;
                }
                if ($product["result"] === Doofinder::DOOFINDER_STATE_UPDATED) {
                    $productsUpdated++;
                }
            }
        }

        $output = sprintf("product created : %s\nproduct updated : %s\n", $productsAdded, $productsUpdated);
        Tlog::getInstance()->info($output);

        return $output;
    }

    public function formatDeletedResponse(array $arrayResults): string
    {
        $productsDeleted = 0;

        foreach ($arrayResults as $results) {
            foreach ($results['results'] as $product) {
                if ($product["result"] === Doofinder::DOOFINDER_STATE_DELETED) {
                    $productsDeleted++;
                }
            }
        }

        $output = sprintf("product deleted : %s \n", $productsDeleted);
        Tlog::getInstance()->info($output);

        return $output;
    }

    private function getImageLink(Product $product): string
    {
        $url = 'legacy-image-library'.DS.'product_image_'.$product->getId()."/full/%5E!525,/0/default.webp";
        return URL::getInstance()->absoluteUrl($url);
    }

    /**
     * @throws PropelException
     */
    private function getAvailability(Product $product): string
    {
        foreach ($product->getProductSaleElementss() as $productSaleElements) {
            if ($productSaleElements->getQuantity() > 0) {
                return "in stock";
            }
        }

        return "out of stock";
    }

    /**
     * @throws PropelException
     */
    private function getProductPrice(Product $product, TaxRule $taxRule, bool $isPromo = false): float|int
    {
        $bestPrice = null;
        $calculator = new Calculator();

        $calculator->loadTaxRuleWithoutProduct(
            $taxRule,
            Country::getShopLocation()
        );

        foreach ($product->getProductSaleElementss() as $productSaleElements) {
            $productPrice = ProductPriceQuery::create()->findOneByProductSaleElementsId($productSaleElements->getId());

            $price = $productPrice?->getPrice();
            if ($isPromo) {
                $price = $productPrice?->getPromoPrice();
            }

            if ($bestPrice === null || $bestPrice > $price) {
                $bestPrice = $price;
            }
        }

        return $calculator->getTaxedPrice($bestPrice ?? "0");
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