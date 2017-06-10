<?php

namespace Cable\Filesystem\Facade;
use Cable\Facade\Facade;

/**
 * Created by PhpStorm.
 * User: vahit
 * Date: 10.06.2017
 * Time: 11:11
 */
class Storage extends Facade
{

    /**
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'storage';
    }
}
