<?php

namespace CreativeSoftTechSolutions\LaravelModules\Facades;

use Illuminate\Support\Facades\Facade;

class Module extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Module';
    }
}
