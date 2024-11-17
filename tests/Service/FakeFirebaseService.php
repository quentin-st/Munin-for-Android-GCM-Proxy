<?php

namespace App\Tests\Service;

use App\Service\FirebaseService;
use Kreait\Firebase\Messaging\MulticastSendReport;

class FakeFirebaseService extends FirebaseService
{
    private ?array $lastPayload = null;

    protected function send(array $regIds, array $payload): MulticastSendReport
    {
        $this->lastPayload = $payload;

        return MulticastSendReport::withItems([]);
    }

    public function getLastPayload(): ?array
    {
        return $this->lastPayload;
    }
}
