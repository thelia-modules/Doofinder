<?php

namespace Doofinder\EventListeners;

use Doofinder\Doofinder;
use Doofinder\Model\DoofinderDfscoreProductQuery;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Event\Product\ProductUpdateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\TheliaFormEvent;
use Thelia\Core\Translation\Translator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DfscoreProductListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_product_modification" => ['addFieldToForm', 128],
            TheliaEvents::PRODUCT_UPDATE  => ['saveFieldForm', 100],
        ];
    }

    public function addFieldToForm(TheliaFormEvent $event): void
    {
        $productId = $event->getForm()->getRequest()->getProductId();
        $dfscoreProduct = DoofinderDfscoreProductQuery::create()->findOneByProductId($productId);
        $dfscore = 1.0;
        if ($dfscoreProduct){
            $dfscore = $dfscoreProduct->getDfscore();
        }
        if (!$dfscoreProduct){
            $dfscore = Doofinder::getConfigValue(Doofinder::DOOFINDER_DEFAULT_CONFIG_DF_SCORE,1.0);
        }
        $event->getForm()->getFormBuilder()->add(
            Doofinder::DOOFINDER_DF_SCORE,
            TextType::class, [
                'required' => false,
                'label' => Translator::getInstance()->trans('Dfscore priority', [], Doofinder::DOMAIN_NAME),
                'label_attr' => array(
                    'help' => Translator::getInstance()->trans(
                        'Dfscore is numeric score boosting. It multiplies the natural score of the item for a search. For instance, if boost is greater than 1.0 the item will appear higher in the results. If it is lower than 1.0, it will appear lower. The minimum value is 0.0.',
                        [],
                        Doofinder::DOMAIN_NAME
                    ),
                ),
                'attr' => [
                    'placeholder' => Translator::getInstance()->trans('ex : 1.2 or 1.5', [], Doofinder::DOMAIN_NAME),
                ],
                'data' => $dfscore,
            ]
        );
    }

    public function saveFieldForm(ProductUpdateEvent $event): void
    {
        $productId = $event->getProductId();
        $dfscoreProduct = DoofinderDfscoreProductQuery::create()->filterByProductId($productId)->findOneOrCreate();
        $dfscore = $event->doofinder_df_score;
        $defaultDfscore = Doofinder::getConfigValue(Doofinder::DOOFINDER_DEFAULT_CONFIG_DF_SCORE,1.0);
        if (empty($dfscore)){
            return;
        }
        $dfscoreProduct
            ->setDfscore($dfscore)
            ->save();
    }
}