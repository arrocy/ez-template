<?php

namespace Arrocy\Template;

use Illuminate\Support\ServiceProvider;

class TemplateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/template.php', 'template');
    }

    public function boot(): void
    {
        $this->loadJsonTranslationsFrom(__DIR__.'/../lang');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'template');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->injectSidebarMenu();
    }

    protected function injectSidebarMenu(): void
    {
        $items = config('menu.sidebar.workspace.items');

        if (isset($items)) {
            $items[] = ['label' => 'Templates', 'icon' => 'fa-solid fa-file-lines', 'route' => 'template.index'];
            config(['menu.sidebar.workspace.items' => $items]);
        }
    }
}