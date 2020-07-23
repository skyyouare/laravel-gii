<?php

namespace Skyyouare\Gii\Facades;

use Illuminate\Support\Facades\Facade;

class Gii extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gii';
    }
}
