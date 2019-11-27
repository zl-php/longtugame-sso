<?php

namespace Longtugme\Sso\Facades;

use Illuminate\Support\Facades\Facade;

class Sso extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sso';
    }
}
