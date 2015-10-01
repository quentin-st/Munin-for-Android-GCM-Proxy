<?php

namespace AppBundle\Model;

class Field
{
    private $label;
    private $value;
    private $warningRange;
    private $criticalRange;
    private $extra;
    /** @var Level */
    private $level;

    public function __construct($label, $value, $wRange, $cRange, $extra, $level)
    {
        $this->label = $label;
        $this->value = $value;
        $this->warningRange = $wRange;
        $this->criticalRange = $cRange;
        $this->extra = $extra;
        $this->level = $level;
    }

    public function toArray()
    {
        return [
            'label' => $this->label,
            'value' => $this->value,
            'w' => $this->warningRange,
            'c' => $this->criticalRange,
            'extra' => $this->extra,
            'level' => $this->level
        ];
    }
}
