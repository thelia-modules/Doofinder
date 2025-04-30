<?php

namespace Doofinder\Twig\Plugins;

use Doofinder\Model\DoofinderExcludedProductQuery;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DoofinderExcludeProductPlugin extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_product_excluded', [$this, 'isProductExcluded']),
        ];
    }

    public function isProductExcluded(?int $productId): bool
    {
        if ($productId === null) {
            return false;
        }

        return DoofinderExcludedProductQuery::create()
            ->filterByProductId($productId)
            ->exists();
    }
}
