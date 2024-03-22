<?php

namespace Doofinder\Service;

use Doofinder\Doofinder;
use Propel\Runtime\Exception\PropelException;
use Thelia\Log\Tlog;
use Thelia\Model\LangQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductPrice;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\RewritingUrlQuery;
use Thelia\Tools\URL;

class DoofinderFormatService
{
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
            'availability' => $this->getAvailability($product->getVisible()),
            'brand' => (string)$product->getBrand()?->setLocale($locale)->getTitle(),
            'categories' => $categories,
            'description' => $product->getDescription(),
            'group_id' => (string)$product->getId(),
            'gtin' => $productSaleElements->getEanCode(),
            'id' => (string)$productSaleElements->getId(),
            'image_link' => $this->getImageLink($product),
            'link' => $this->getProductLink($product, $locale),
            //'mpn' => '', //référence fabricant ?
            'best_price' => (string)$this->getPseProductPrice($productSaleElements->getId())?->getPromoPrice(),
            'sale_price' => (string)$this->getPseProductPrice($productSaleElements->getId())?->getPrice(),
            'title' => $product->setLocale($locale)->getTitle(),
        ];
    }

    public function formatResponse(array $results): string
    {
        $productsAdded = 0;
        $productsUpdated = 0;
        $productsDeleted = 0;

        foreach ($results['results'] as $product) {
            if ($product["result"] === Doofinder::DOOFINDER_STATE_CREATED) {
                $productsAdded++;
            }
            if ($product["result"] === Doofinder::DOOFINDER_STATE_UPDATED) {
                $productsUpdated++;
            }
            if ($product["result"] === Doofinder::DOOFINDER_STATE_DELETED) {
                $productsDeleted++;
            }
        }

        $output = sprintf("product created : %s, product updated : %s, product deleted : %s ", $productsAdded, $productsUpdated, $productsDeleted);
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

    private function getAvailability(bool $visible): string
    {
        if ($visible) {
            return "in stock";
        }

        return "out of stock";
    }
    private function getPseProductPrice(int $pseId): ?ProductPrice
    {
        return ProductPriceQuery::create()
            ->useCurrencyQuery()
            ->filterByByDefault(1)
            ->endUse()
            ->findOneByProductSaleElementsId($pseId);
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