<?php

namespace Doofinder\Controller;

use Doofinder\Doofinder;
use Doofinder\Form\ConfigurationForm;
use Doofinder\Form\FrontHooksForm;
use Doofinder\Service\ApiDoofinderManagementService;
use Doofinder\Shared\Exceptions\ApiException;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\AdminController;
use Thelia\Core\Template\ParserContext;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;

#[Route('/admin/module/Doofinder', name: 'admin_doofinder_config_')]
class ConfigurationController extends AdminController
{
    #[Route('/configuration', name: 'configuration', methods: 'POST')]
    public function saveConfiguration(
        ParserContext $parserContext,
        ApiDoofinderManagementService $apiDoofinderManagementService
    ): RedirectResponse|Response
    {
        $form = $this->createForm(ConfigurationForm::getName());
        try {
            $data = $this->validateForm($form)->getData();

            Doofinder::setConfigValue(Doofinder::DOOFINDER_SEARCH_ZONE_CONFIG_KEY, $data["search_zone"]);
            Doofinder::setConfigValue(Doofinder::DOOFINDER_HASH_ID_CONFIG_KEY, $data["hash_id"]);
            Doofinder::setConfigValue(Doofinder::DOOFINDER_USER_ID_CONFIG_KEY, $data["user_id"]);
            Doofinder::setConfigValue(Doofinder::DOOFINDER_USER_TOKEN_CONFIG_KEY, $data["user_token"]);

            return $this->generateSuccessRedirect($form);
        } catch (FormValidationException $e) {
            $errorMessage = $this->createStandardFormValidationErrorMessage($e);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }

        $form->setErrorMessage($errorMessage);

        $parserContext
            ->addForm($form)
            ->setGeneralError($errorMessage);

        return $this->generateErrorRedirect($form);
    }

    #[Route('/front_hooks', name: 'front_hooks', methods: 'POST')]
    public function saveFrontHooksParameters(ParserContext $parserContext): RedirectResponse|Response
    {
        $form = $this->createForm(FrontHooksForm::getName());
        try {
            $data = $this->validateForm($form)->getData();

            Doofinder::setConfigValue(Doofinder::DOOFINDER_HOOK_SEARCH_SCRIPT_CONFIG_KEY, $data["hook_search_script"]);
            Doofinder::setConfigValue(Doofinder::DOOFINDER_BASIC_SEARCH_BAR_CONFIG_KEY, (bool) $data["basic_search_bar"]);
            Doofinder::setConfigValue(Doofinder::DOOFINDER_QUERY_INPUT_ID_CONFIG_KEY, $data["query_input_id"]);

            return $this->generateSuccessRedirect($form);
        } catch (FormValidationException $e) {
            $errorMessage = $this->createStandardFormValidationErrorMessage($e);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }

        $form->setErrorMessage($errorMessage);

        $parserContext
            ->addForm($form)
            ->setGeneralError($errorMessage);

        return $this->generateErrorRedirect($form);
    }
}