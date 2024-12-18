<?php

namespace App\Controller;

use App\Model\Alert;
use App\Model\Field;
use App\Model\Level;
use App\Repository\StatRepository;
use App\Service\FirebaseService;
use App\Service\MailService;
use Exception;
use JsonException;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProxyController extends AbstractController
{
    public function __construct(
        private readonly FirebaseService $firebaseService,
        private readonly StatRepository $statRepo,
    ) {
    }

    /**
     * Redirects to www.munin-for-android.com
     */
    #[Route('/', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirect('https://www.munin-for-android.com');
    }

    /**
     * Allows uptime checks
     */
    #[Route("/ping", methods: ["GET"])]
    public function ping(): Response
    {
        return new Response('pong');
    }

    /**
     * Called by GCM-Trigger. Must contain following information:
     *  - reg_ids: comma-separated ids list
     *  - data
     */
    #[Route("/trigger/declareAlert", name: "declareAlert", methods: ["POST"])]
    public function declareAlert(Request $request, LoggerInterface $logger): Response
    {
        // Check POST params
        $post = $request->request;

        if (true !== $check = $this->checkParams(['reg_ids', 'data'], $post)) {
            return $check;
        }

        try {
            $reg_ids = json_decode($post->get('reg_ids'), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return $this->onError('Invalid reg_ids: ' . $e->getMessage());
        }

        // Parse data
        $dataString = $post->get('data');
        $xml = @simplexml_load_string($dataString);
        $helpDiagnose = $post->get('help_diagnose', false);
        $contributeToStats = $post->get('contribute_to_stats', true);

        if ($contributeToStats) {
            $this->updateStats();
        }

        if ($xml === false) {
            // There's an error
            $errors = implode("\n", libxml_get_errors());

            return $this->onError('Error processing input: ' . $errors);
        }

        try {
            // Build alerts list
            $alerts = [];

            /** @var SimpleXMLElement $alert */
            foreach ($xml->alert as $alert) {
                $attrs = $alert->attributes() ?? [];
                $group = (string) $attrs['group'];
                $host = (string) $attrs['host'];
                $graph_category = (string) $attrs['graph_category'];
                $graph_title = (string) $attrs['graph_title'];

                $a = new Alert($group, $host, $graph_category, $graph_title);

                // Find fields
                /** @var SimpleXMLElement $field */
                foreach ($alert->children() as $field) {
                    $level = $field->getName();
                    if (!in_array($level, ['warning', 'critical', 'unknown'])) {
                        continue;
                    }

                    $attrs = $field->attributes() ?? [];
                    $a->addField(new Field(
                        (string) $attrs['label'],
                        (string) $attrs['value'],
                        (string) $attrs['w'],
                        (string) $attrs['c'],
                        (string) $attrs['extra'],
                        Level::fromLabel($level)
                    ));
                }

                $alerts[] = $a;
            }

            // Notify devices
            $report = $this->firebaseService->notifyAlerts($reg_ids, $alerts);
            $logs = $this->firebaseService->parseMulticastReport($report);

            return $this->onSuccess([
                'success' => true,
                'logs' => $logs,
            ]);
        } catch (Exception $ex) {
            $logger->error($ex->getMessage());
            return $this->onError('Error processing input');
        }
    }

    /**
     * Increments internal stats
     */
    private function updateStats(): void
    {
        $this->statRepo->incrementStat();
    }

    /**
     * Called by GCM-Trigger. Must contain following information:
     *  - reg_ids: comma-separated ids list
     */
    #[Route("/trigger/test", name: "test", methods: ["POST"])]
    public function testAction(Request $request): Response
    {
        // Check POST params
        $post = $request->request;

        if (true !== $check = $this->checkParams(['reg_ids'], $post)) {
            return $check;
        }

        $reg_ids = json_decode($post->get('reg_ids'), true, 512, JSON_THROW_ON_ERROR);

        // Notify each device
        $this->firebaseService->test($reg_ids);

        return $this->onSuccess([
            'success' => true,
        ]);
    }

    /**
     * Called by Android devices from notifications settings screen
     */
    #[Route("/android/sendConfig", methods: ["POST"])]
    public function sendConfigByMailAction(Request $request, MailService $mailService): Response
    {
        // Check POST params
        $post = $request->request;

        if (true !== $check = $this->checkParams(['mailAddress', 'regId'], $post)) {
            return $check;
        }

        $mailService->sendInstructionsMail(
            $post->get('mailAddress'),
            $post->get('regId')
        );

        return new JsonResponse();
    }

    #[Route("/stats/get", methods: ["GET"])]
    public function getStats(): Response
    {
        $stat = $this->statRepo->getStat();

        return new JsonResponse([
            'hits_count' => $stat->hitsCount,
            'last_hit' => $stat->lastHit->getTimestamp()
        ]);
    }

    private function checkParams(array $requiredParams, ParameterBag $post): bool|JsonResponse
    {
        foreach ($requiredParams as $param) {
            if (!$post->has($param)) {
                return $this->onError('Missing param: ' . $param);
            }
        }
        return true;
    }

    private function onError(string $message): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message
        ]);
    }

    private function onSuccess(array $data): JsonResponse
    {
        return new JsonResponse($data);
    }
}
