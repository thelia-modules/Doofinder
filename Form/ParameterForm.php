<?php

namespace Doofinder\Form;

use Doofinder\Doofinder;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

class ParameterForm extends BaseForm
{
    protected function buildForm(): void
    {
        $this->formBuilder
            ->add(
                'real_time_sync', CheckboxType::class, [
                    'required' => false,
                    'label' => Translator::getInstance()->trans('Use real-time synchronization for your product ?', [], Doofinder::DOMAIN_NAME),
                    'data' => (bool) Doofinder::getConfigValue(Doofinder::DOOFINDER_REAL_TIME_SYNC_CONFIG_KEY),
                    'label_attr' => array(
                        'for' => 'real_time_sync',
                        'help' => Translator::getInstance()->trans('(disable it if you are using ApiPlatform)', [], Doofinder::DOMAIN_NAME),
                    ),
                ]
            )
        ;
    }
}