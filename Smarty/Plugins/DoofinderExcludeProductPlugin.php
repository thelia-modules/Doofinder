<?php

namespace Doofinder\Smarty\Plugins;

use Doofinder\Doofinder;
use Doofinder\Model\DoofinderExcludedProductQuery;
use TheliaSmarty\Template\AbstractSmartyPlugin;
use TheliaSmarty\Template\SmartyPluginDescriptor;

class DoofinderExcludeProductPlugin extends AbstractSmartyPlugin
{
    public function getPluginDescriptors(): array
    {
        return [
            new SmartyPluginDescriptor('function', 'getExcludeProduct', $this, 'getExcludeProduct'),
            new SmartyPluginDescriptor('function', 'doofinderHashId', $this, 'getDoofinderHashId'),
        ];
    }

    public function getExcludeProduct($param, $smarty): void
    {
        if (isset($param['product_id'])) {
            $excludeProduct = DoofinderExcludedProductQuery::create()->findOneByProductId($param['product_id']);

            if (null !== $excludeProduct) {
                $smarty->assign('isExclude', true);
                return;
            }
        }

        $smarty->assign('isExclude', false);
    }
    public function getDoofinderHashId($param, $smarty): string
    {
        return Doofinder::getConfigValue(Doofinder::DOOFINDER_HASH_ID_CONFIG_KEY);
    }
}
