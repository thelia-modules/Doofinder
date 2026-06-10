<?php

namespace Doofinder\Hook;

use Doofinder\Model\DoofinderExcludedProductQuery;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class ExcludedProductHook extends BaseHook
{
    public function productEditBottom(HookRenderEvent $event): void
    {
        $productId = (int) $event->getArgument('product_id');

        $event->add(
            $this->render(
                "Doofinder/excluded_product_form.html.twig",
                [
                    'product_id' => $productId,
                    'is_excluded' => null !== DoofinderExcludedProductQuery::create()->findOneByProductId($productId),
                ]
            )
        );
    }

    public static function getSubscribedHooks(): array
    {
        return [
            "product-edit.bottom" => [
                [
                    "type" => "back",
                    "method" => "productEditBottom"
                ],
            ]
        ];
    }
}
