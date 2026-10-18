<?php

declare(strict_types=1);

namespace Doofinder\Hook\Theme;

use Doofinder\Doofinder;
use Thelia\Core\Hook\Theme\ThemeHookInterface;
use Twig\Environment;

final class DoofinderThemeHook implements ThemeHookInterface
{
    private ?string $configuredHook = null;

    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function supports(string $hookName): bool
    {
        // supports() runs for every theme hook of the page: read the setting once.
        $this->configuredHook ??= (string) Doofinder::getConfigValue(Doofinder::DOOFINDER_HOOK_SEARCH_SCRIPT_CONFIG_KEY, '');

        return '' !== $this->configuredHook && $this->configuredHook === $hookName;
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
