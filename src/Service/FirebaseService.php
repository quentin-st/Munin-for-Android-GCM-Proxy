<?php

namespace App\Service;

use App\Model\Alert;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;

class FirebaseService
{
    private Client $client;

    public function __construct(string $gcmApiKey)
    {
        $this->client = new Client([
            RequestOptions::HEADERS => [
                'Authorization' => 'key='.$gcmApiKey,
            ],
        ]);
    }

    /**
     * Notifies devices about alerts
     * @param string[] $regIds
     * @param Alert[] $alerts
     */
    public function notifyAlerts(array $regIds, array $alerts): bool
    {
        $alertsArray = array_map(static fn(Alert $alert) => $alert->toArray(), $alerts);

        return $this->send($regIds, [
            'alerts' => $alertsArray
        ]);
    }

    /**
     * @param string[] $regIds
     */
    public function test(array $regIds): bool
    {
        return $this->send($regIds, ['test' => true]);
    }

    /**
     * @param string[] $regIds
     */
    private function send(array $regIds, array $payload): bool
    {
        $results = [];

        foreach ($regIds as $regId) {
            try {
                $this->client->post('https://fcm.googleapis.com/fcm/send', [
                    RequestOptions::JSON => [
                        'to' => $regId,
                        'data' => $payload,
                    ],
                ]);

                $results[] = true;
            } catch (ClientException|ServerException) {
                $results[] = false;
            }
        }

        return count(array_filter($results)) === count($results);
    }
}
