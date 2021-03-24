<?php

namespace App\Model;

class Level
{
    public const CRITICAL = 'c';
    public const WARNING = 'w';
    public const UNKNOWN = 'u';

    public static function fromLabel(?string $label): string
    {
        switch ($label) {
            case 'critical': return self::CRITICAL;
            case 'warning': return self::WARNING;
            default: return self::UNKNOWN;
        }
    }
}
