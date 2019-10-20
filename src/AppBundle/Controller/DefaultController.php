<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProxyError;
use AppBundle\Model\Alert;
use AppBundle\Model\Field;
use AppBundle\Model\Level;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BaseController
{
    /**
     * Redirects to www.munin-for-android.com
     * @Route("/")
     * @Method({"GET"})
     */
    public function indexAction()
    {
        return $this->redirect('https://www.munin-for-android.com');
    }

    /**
     * Called by GCM-Trigger. Must contain following information:
     *  - reg_ids: comma-separated ids list
     *  - data
     * @Route("/trigger/declareAlert", name="declareAlert")
     * @Method({"POST"})
     */
    public function declareAlertAction(Request $request)
    {
        // Check POST params
        $post = $request->request;

        $check = $this->checkParams(['reg_ids', 'data'], $post);
        if ($check !== true)
            return $check;

        $reg_ids = json_decode($post->get('reg_ids'), true);

        // Parse data
        $dataString = $post->get('data');
        $xml = simplexml_load_string($dataString);
        $helpDiagnose = $post->get('help_diagnose', false);
        $contributeToStats = $post->get('contribute_to_stats', true);

        if ($contributeToStats)
            $this->updateStats();

        if ($xml === false) {
            // There's an error
            $errors = implode("\n", libxml_get_errors());

            if ($helpDiagnose)
                $this->storeAndSendDiagnostic($request, $errors, $dataString);

            return $this->onError('Error processing input: ' . $errors);
        }

        try {
            // Build alerts list
            $alerts = [];

            /** @var \SimpleXMLElement $alert */
            foreach ($xml->alert as $alert) {
                $attrs = $alert->attributes();
                $group = (string) $attrs['group'];
                $host = (string) $attrs['host'];
                $graph_category = (string) $attrs['graph_category'];
                $graph_title = (string) $attrs['graph_title'];

                $a = new Alert($group, $host, $graph_category, $graph_title);

                if (!$a->isValid() && $helpDiagnose)
                    $this->storeAndSendDiagnostic($request, 'Null value', $dataString);

                // Find fields
                /** @var \SimpleXMLElement $field */
                foreach ($alert->children() as $field)
                {
                    $level = $field->getName();
                    if (!in_array($level, ['warning', 'critical', 'unknown']))
                        continue;

                    $attrs = $field->attributes();
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
            $this->get('app.firebase')->notifyAlerts($reg_ids, $alerts);

            return $this->onSuccess();
        } catch (\Exception $ex) {
            if ($helpDiagnose)
                $this->storeAndSendDiagnostic($request, $ex->getMessage(), $dataString);

            return $this->onError('Error processing input: ' . $ex->getMessage());
        }
    }

    /**
     * If help_diagnose was explicitly set to true, store error & send mail to maintainer
     * @param Request $request
     * @param $exception
     * @param $structure
     */
    private function storeAndSendDiagnostic(Request $request, $exception, $structure)
    {
        $em = $this->getDoctrine()->getManager();

        $error = new ProxyError();
        $error
            ->setSource($request->getClientIp())
            ->setException($exception)
            ->setStructure($structure);

        $em->persist($error);
        $em->flush();

        if ($this->getParameter('send_mail_on_error'))
            $this->get('app.mail_service')->sendProxyExceptionMail($error);
    }

    /**
     * Increments internal stats
     */
    private function updateStats(): void
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AppBundle:Stat')->incrementStat();
    }

    /**
     * Called by GCM-Trigger. Must contain following information:
     *  - reg_ids: comma-separated ids list
     * @Route("/trigger/test", name="test")
     * @Method({"POST"})
     */
    public function testAction(Request $request)
    {
        // Check POST params
        $post = $request->request;

        $check = $this->checkParams(['reg_ids'], $post);
        if ($check !== true) {
            return $check;
        }

        $reg_ids = json_decode($post->get('reg_ids'), true);

        // Notify each device
        $this->get('app.firebase')->test($reg_ids);

        return $this->onSuccess();
    }

    /**
     * Called by Android devices from notifications settings screen
     * @Route("/android/sendConfig")
     * @Method({"POST"})
     */
    public function sendConfigByMailAction(Request $request)
    {
        // Check POST params
        $post = $request->request;

        if (true !== $check = $this->checkParams(['mailAddress', 'regId'], $post)) {
            return $check;
        }

        $this->get('app.mail_service')->sendInstructionsMail(
            $post->get('mailAddress'),
            $post->get('regId')
        );

        return new JsonResponse();
    }

    /**
     * @Route("/stats/get")
     * @Method({"GET"})
     */
    public function getStats()
    {
        $em = $this->getDoctrine()->getManager();
        $stat = $em->getRepository('AppBundle:Stat')->getStat();

        return new JsonResponse([
            'hits_count' => $stat->getHitsCount(),
            'last_hit' => $stat->getLastHit()->getTimestamp()
        ]);
    }
}
