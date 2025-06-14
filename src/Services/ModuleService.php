<?php

namespace CreativeSoftTechSolutions\LaravelModules\Services;

use Illuminate\Support\Facades\File;

class ModuleService
{
    public function modulePath($path = '')
    {
        if (!config('modules.path')) {
            throw new \Exception('Modules path is not defined in the configuration.');
        }

        return config('modules.path') . ($path ? '/' . $path : '');
    }

    public function getAvailableModules()
    {
        $modulesPath = config('modules.path');

        if (!is_dir($modulesPath)) {
            return [];
        }

        $iterator = new \FilesystemIterator($modulesPath, \FilesystemIterator::SKIP_DOTS);

        $modules = [];
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                $dirName = $fileInfo->getFilename();
                $moduleConfigPath = $modulesPath . "/$dirName/Config/config.php";
                if (File::exists($moduleConfigPath)) {
                    $moduleConfig = require $moduleConfigPath;
                    if (!is_array($moduleConfig)) {
                        continue;
                    }
                    $modules[$dirName] = $moduleConfig['name'];
                }
            }
        }

        return $modules;
    }

    public function loadPlugins(?array $modules = []): void
    {
        $loadedPlugins = [];
        foreach ($modules as $module) {
            if ($this->registerPlugin($module)) {
                $loadedPlugins[] = $module;
            }
        }

        app()->instance('loadedPlugins', $loadedPlugins);
        $this->mergeModuleConfigs($loadedPlugins);
    }

    private function registerPlugin(string $module): bool
    {
        $modulePath = config('modules.path') . "/{$module}";
        if (File::exists($modulePath)) {
            $serviceProvider = "Modules\\{$module}\\Providers\\{$module}ServiceProvider";
            if (class_exists($serviceProvider)) {
                app()->register($serviceProvider);
                return true;
            }
        }

        return false;
    }

    private function mergeModuleConfigs(?array $loadedPlugins = null): void
    {
        if (is_null($loadedPlugins)) {
            $loadedPlugins = app()->make('loadedPlugins', []);
        }

        foreach ($loadedPlugins as $module) {
            $modulePath = config('modules.path') . "/{$module}";
            if (!is_dir($modulePath)) {
                return;
            }

            $configPath = $modulePath . '/Config';
            if (is_dir($configPath)) {
                foreach (scandir($configPath) as $file) {
                    if (
                        pathinfo($file, PATHINFO_EXTENSION) === 'php' &&
                        $file !== 'config.php' &&
                        str_ends_with($file, '_config.php')
                    ) {
                        $moduleConfig = require $configPath . '/' . $file;
                        if (!is_array($moduleConfig)) {
                            continue;
                        }
                        $configKey = pathinfo($file, PATHINFO_FILENAME);
                        $baseConfig = config($configKey) ?? [];
                        $baseConfig = array_merge_recursive($baseConfig, $moduleConfig);
                        config([$configKey => $baseConfig]);
                    }
                }
            }
        }
    }
}
