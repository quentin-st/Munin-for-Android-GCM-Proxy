<?php

namespace AppBundle\Service;

use Endroid\Gcm\Client;

class GCMService
{
    /** @var Client */
    private $gcmClient;

    public function __construct(Client $gcmClient)
    {
        $this->gcmClient = $gcmClient;
    }

    public function notifyAlert()
    {

    }
}
