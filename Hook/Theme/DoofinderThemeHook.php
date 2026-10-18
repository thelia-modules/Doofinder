<?php

declare(strict_types=1);

namespace Doofinder\Hook\Theme;

use Doofinder\Doofinder;
use Thelia\Core\Hook\Theme\ThemeHookInterface;
use Twig\Environment;

final readonly class DoofinderThemeHook implements ThemeHookInterface
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function supports(string $hookName): bool
    {
        $configuredHook = Doofinder::getConfigValue(Doofinder::DOOFINDER_HOOK_SEARCH_SCRIPT_CONFIG_KEY);

        return \is_string($configuredHook) && '' !== $configuredHook && $configuredHook === $hookName;
    }

    public function render(string $hookName, array $parameters): string
    {
        return $this->twig->render('@DoofinderModule/theme-hook/search-script.html.twig', [
            'query_input_id' => (string) Doofinder::getConfigValue(Doofinder::DOOFINDER_QUERY_INPUT_ID_CONFIG_KEY),
            'hash_id' => (string) Doofinder::getConfigValue(Doofinder::DOOFINDER_HASH_ID_CONFIG_KEY),
            'search_zone' => (string) Doofinder::getConfigValue(Doofinder::DOOFINDER_SEARCH_ZONE_CONFIG_KEY),
            'basic_input' => (bool) Doofinder::getConfigValue(Doofinder::DOOFINDER_BASIC_SEARCH_BAR_CONFIG_KEY),
        ]);
    }
}
