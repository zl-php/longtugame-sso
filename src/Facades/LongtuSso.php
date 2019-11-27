<?php

namespace Longtugame\Sso\Facades;

use Illuminate\Support\Facades\Facade;

class LongtuSso extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'longtusso';
    }
}
