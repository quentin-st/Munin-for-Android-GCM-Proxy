<?php

namespace AppBundle\Service;

use AppBundle\Model\Alert;
use Endroid\Gcm\Client;

class GCMService
{
    /** @var Client */
    private $gcmClient;

    public function __construct(Client $gcmClient)
    {
        $this->gcmClient = $gcmClient;
    }

    public function notifyAlert(array $regIds, Alert $alert)
    {
        $data = $alert->toArray();

        return $this->gcmClient->send($data, $regIds);
    }
}