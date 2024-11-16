<?php

namespace App\Tests\Service;

use App\Service\FirebaseService;

class FakeFirebaseService extends FirebaseService
{
    private ?array $lastPayload = null;

    protected function send(array $regIds, array $payload): bool
    {
        $this->lastPayload = $payload;

        return true;
    }

    public function getLastPayload(): ?array
    {
        return $this->lastPayload;
    }
}
