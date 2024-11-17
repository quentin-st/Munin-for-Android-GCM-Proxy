<?php

namespace App\Service;

use App\Model\Alert;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\Messaging\ApiConnectionFailed;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\SendReport;
use Psr\Log\LoggerInterface;

class FirebaseService
{
    public function __construct(
        private readonly string $env,
        private readonly Messaging $messaging,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Notifies devices about alerts
     * @param string[] $regIds
     * @param Alert[] $alerts
     */
    public function notifyAlerts(array $regIds, array $alerts): MulticastSendReport
    {
        $alertsArray = array_map(static fn(Alert $alert) => $alert->toArray(), $alerts);

        return $this->send($regIds, [
            'alerts' => json_encode($alertsArray, JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * @param string[] $regIds
     */
    public function test(array $regIds): MulticastSendReport
    {
        return $this->send($regIds, ['test' => true]);
    }

    /**
     * @param string[] $regIds
     */
    protected function send(array $regIds, array $payload): MulticastSendReport
    {
        if ($this->env !== 'prod') {
            return MulticastSendReport::withItems([]);
        }

        $messages = array_map(
            static fn(string $regId) => CloudMessage::withTarget(MessageTarget::TOKEN, $regId)
                ->withData($payload),
            $regIds
        );
        try {
            return $this->messaging->sendAll($messages);
        } catch (FirebaseException|MessagingException|ApiConnectionFailed $ex) {
            $this->logger->error($ex->getMessage());
            return MulticastSendReport::withItems([]);
        }
    }

    public function parseMulticastReport(MulticastSendReport $report): array
    {
        $pushTokens = array_map(static fn (SendReport $report) => $report->target()->value(), $report->getItems());
        $errors = array_fill_keys($pushTokens, 'ok');

        foreach ($report->failures()->getItems() as $failure) {
            $target = $failure->target();
            $pushToken = $target->value();

            $errors[$pushToken] = match (true) {
                $failure->messageTargetWasInvalid() => 'message-target-invalid',
                $failure->messageWasInvalid() => 'message-invalid',
                $failure->messageWasSentToUnknownToken() => 'token-not-found',
                default => $failure->error()?->getMessage(),
            };
        }

        return $errors;
    }
}
