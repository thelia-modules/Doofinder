<?php

namespace Doofinder\Form;

use Doofinder\Doofinder;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

class FrontHooksForm extends BaseForm
{
    protected function buildForm(): void
    {
        $this->formBuilder
            ->add(
                'hook_search_script',
                TextType::class, [
                    'required' => false,
                    'label' => Translator::getInstance()->trans('Hook Search Script', [], Doofinder::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => 'search_zone',
                        'help' => Translator::getInstance()->trans("hook of doofinder search script. Use a theme hook name such as layout.head.bottom or layout.body.bottom", [], Doofinder::DOMAIN_NAME),
                    ],
                    'data' => Doofinder::getConfigValue(Doofinder::DOOFINDER_HOOK_SEARCH_SCRIPT_CONFIG_KEY)
                ]
            )
            ->add(
                'query_input_id',
                TextType::class, [
                    'required' => false,
                    'label' => Translator::getInstance()->trans('Id of the search Bar', [], Doofinder::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => 'search_zone',
                        'help' => Translator::getInstance()->trans("id of doofinder search bar", [], Doofinder::DOMAIN_NAME),
                    ],
                    'data' => Doofinder::getConfigValue(Doofinder::DOOFINDER_QUERY_INPUT_ID_CONFIG_KEY)
                ]
            )
            ->add(
                'basic_search_bar',
                CheckboxType::class, [
                    'required' => false,
                    'label' => Translator::getInstance()->trans('Add a basic html input for search', [], Doofinder::DOMAIN_NAME),
                    'data' => (bool) Doofinder::getConfigValue(Doofinder::DOOFINDER_BASIC_SEARCH_BAR_CONFIG_KEY)
                ]
            )
        ;
    }
}