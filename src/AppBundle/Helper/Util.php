<?php

namespace AppBundle\Helper;

abstract class Util
{
    public static function randomHex($length=20)
    {
        $val = '';
        for($i=0; $i<$length; $i++)
            $val .= chr(rand(65, 90));
        return $val;
    }
}
