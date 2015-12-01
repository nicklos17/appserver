<?php

namespace Appserver\Utils;

use Appserver\Utils\Extensive\ExtensiveTecent as tecentClass;
use Appserver\Utils\Extensive\ExtensiveSina as SinaClass;

class UserFactory
{

    public static function operateMethod($type, $di)
    {
        switch($type)
        {
            //sina第三方
            case 1:
                return new SinaClass($di['sysconfig']['swooleConfig']['ip'],
                                    $di['sysconfig']['swooleConfig']['port']);
            //qq第三方
            case 3:
                return new TecentClass($di['sysconfig']['swooleConfig']['ip'],
                                    $di['sysconfig']['swooleConfig']['port']);
        }
    }

}

