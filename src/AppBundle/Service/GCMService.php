<?php

namespace AppBundle\Service;

class GCMService
{
    private $gcmApiKey;

    public function __construct($gcmApiKey)
    {
        $this->gcmApiKey = $gcmApiKey;
    }

    public function notifyAlert()
    {

    }
}
