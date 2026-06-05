<?php

namespace Doofinder\Service;

use Doofinder\Doofinder;
use Doofinder\Model\DoofinderDfscoreProductQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use Thelia\Log\Tlog;
use Thelia\Model\Base\FeatureProductQuery;
use Thelia\Model\Country;
use Thelia\Model\LangQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductImageQuery;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\TaxRule;
use Thelia\TaxEngine\Calculator;
use Thelia\TaxEngine\TaxEngine;
use Thelia\Tools\URL;

class DoofinderFormatService
{
    public function __construct(protected TaxEngine $taxEngine) {}

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
        $productId = $product->getId();
        $dfscoreProduct = DoofinderDfscoreProductQuery::create()->findOneByProductId($productId);
        $dfscore = 1.0;
        if ($dfscoreProduct){
            $dfscore = $dfscoreProduct->getDfscore();
        }
        if (!$dfscoreProduct || !is_numeric($dfscore) || (int)$dfscore < 0){
            $dfscore = Doofinder::getConfigValue(Doofinder::DOOFINDER_DEFAULT_CONFIG_DF_SCORE,1.0);
        }
        if (!$this->hasValidPrice($product)){
            throw new \RuntimeException('No valid sale elements found for product with ref : '.$product->getRef(). '. Please ensure each sale element has a price greater than zero.');
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
            'mpn' => $product->getRef(),
            'df_manual_boost' => (float)$dfscore,
            'best_price' => (string)$this->getProductPrice($product, $product->getTaxRule(), true),
            'sale_price' => (string)$this->getProductPrice($product, $product->getTaxRule()),
            'title' => $product->setLocale($locale)->getTitle(),
            'features' => $features
        ];
    }
    /**
     * @throws PropelException
     */
    public function formatIndexImportDelete(int $productId): array
    {
        return [
            'id' => (string)$productId,
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
        $image = ProductImageQuery::create()->filterByProductId($product->getId())->orderByPosition()->find()->getFirst();

        if (!$image) {
            return '';
        }

        $url = 'legacy-image-library' . DS . 'product_image_' . $image->getId() . "/full/%5E!525,/0/default.webp";

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
            if (!$productSaleElements->getIsDefault()) {
                continue;
            }
            $productPrice = ProductPriceQuery::create()->findOneByProductSaleElementsId($productSaleElements->getId());

            $price = $productPrice?->getPrice();

            // To prevent doofinder error making best_price = sale_price : if promo, put promo_price in sale_price
            if ($productSaleElements->getPromo()){
                $bestPrice = $productPrice?->getPromoPrice();
            }

            if ($isPromo) {
                $bestPrice = $productPrice?->getPromoPrice();
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

    private function hasValidPrice(Product $product): bool
    {
        foreach ($product->getProductSaleElementss() as $productSaleElements) {
            $productPrice = ProductPriceQuery::create()->findOneByProductSaleElementsId($productSaleElements->getId());
            if ($productPrice?->getPrice() > 0) {
                return true;
            }
        }
        return false;
    }
}
