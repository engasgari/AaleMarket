<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $modulesPath = app_path('Modules');

        if (! File::isDirectory($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $modulePath) {
            $routesWeb = $modulePath.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php';
            $routesApi = $modulePath.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'api.php';
            $viewsPath = $modulePath.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'views';

            if (File::exists($routesWeb)) {
                $this->loadRoutesFrom($routesWeb);
            }

            if (File::exists($routesApi)) {
                $this->loadRoutesFrom($routesApi);
            }

            if (File::isDirectory($viewsPath)) {
                $moduleName = basename($modulePath);
                $this->loadViewsFrom($viewsPath, $moduleName);
            }
        }
    }
}
