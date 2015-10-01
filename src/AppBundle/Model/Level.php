<?php

namespace AppBundle\Model;

class Level
{
    const CRITICAL = 'c';
    const WARNING = 'w';
    const UNKNOWN = 'u';

    public static function fromLabel($label)
    {
        switch ($label)
        {
            case 'critical': return Level::CRITICAL;
            case 'warning': return Level::WARNING;
            case 'unknown': return Level::UNKNOWN;
            default: return Level::UNKNOWN;
        }
    }
}
