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

    /**
     * Notifies devices about alerts
     * @param array $regIds
     * @param Alert[] $alerts
     * @return bool
     */
    public function notifyAlerts(array $regIds, array $alerts)
    {
        $alertsArray = [];
        foreach ($alerts as $alert)
            $alertsArray[] = $alert->toArray();

        return $this->gcmClient->send([
            'alerts' => $alertsArray
        ], $regIds);
    }

    public function test(array $regIds)
    {
        return $this->gcmClient->send(['test' => true], $regIds);
    }

    public function getClient()
    {
        return $this->gcmClient;
    }
}
