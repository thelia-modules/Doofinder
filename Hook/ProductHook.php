<?php

namespace Doofinder\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class ProductHook extends BaseHook
{
    public function onProductEditRightColumnBottom(HookRenderEvent $event): void
    {
        $event->add(
            $this->render(
                "hooks/dfscore.html",
            )
        );
    }
    public static function getSubscribedHooks(): array
    {
        return [
            "product.modification.form-right.bottom" => [
                [
                    "type" => "back",
                    "method" => "onProductEditRightColumnBottom"
                ],
            ]
        ];
    }
}