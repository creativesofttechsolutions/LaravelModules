<?php

// Helper function to get the module path

use CreativeSoftTechSolutions\LaravelModules\Facades\Module;

if (!function_exists('module_path')) {
    /**
     * Get the path to the modules directory.
     *
     * @param string $path
     * @return string
     */
    function module_path($path = '')
    {
        return Module::modulePath($path);
    }
}

if (!function_exists('get_available_modules')) {
    /**
     * Get a list of available modules.
     *
     * @return array
     */
    function get_available_modules()
    {
        return Module::getAvailableModules();
    }
}

/**
 * Load and register plugins.
 */
function loadPlugins(?array $modules = []): void
{
    Module::loadPlugins($modules);
}

