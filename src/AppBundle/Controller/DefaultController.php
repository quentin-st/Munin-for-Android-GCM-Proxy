<?php

namespace AppBundle\Controller;

use AppBundle\Model\Alert;
use AppBundle\Model\Field;
use AppBundle\Model\Level;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
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
        $array = json_decode(json_encode(simplexml_load_string($dataString)), true);

        // Build alerts list
        $alerts = [];
        foreach ($array as $alert)
        {
            $a = new Alert(
                $alert['@attributes']['group'],
                $alert['@attributes']['host'],
                $alert['@attributes']['graph_category'],
                $alert['@attributes']['graph_title']
            );

            // Find fields
            foreach (['warning', 'critical', 'unknown'] as $level)
            {
                if (array_key_exists($level, $alert))
                {
                    $a->addField(new Field(
                        $alert[$level]['@attributes']['label'],
                        $alert[$level]['@attributes']['value'],
                        $alert[$level]['@attributes']['w'],
                        $alert[$level]['@attributes']['c'],
                        $alert[$level]['@attributes']['extra'],
                        Level::fromLabel($level)
                    ));
                }
            }

            $alerts[] = $a;
        }

        // Notify devices
        $this->get('app.gcm')->notifyAlerts($reg_ids, $alerts);

        return $this->onSuccess();
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
        if ($check !== true)
            return $check;

        $reg_ids = json_decode($post->get('reg_ids'), true);

        // Notify each device
        $this->get('app.gcm')->test($reg_ids);

        return $this->onSuccess();
    }

    /**
     * @Route("/android/sendConfig")
     * @Method({"POST"})
     */
    public function sendConfigByMailAction(Request $request)
    {
        // Check POST params
        $post = $request->request;

        $check = $this->checkParams(['mailAddress', 'regId'], $post);
        if ($check !== true)
            return $check;

        $this->get('app.mail_service')->sendInstructionsMail(
            $post->get('mailAddress'),
            $post->get('regId')
        );

        return new JsonResponse();
    }

    /**
     * @param array $requiredParams
     * @param ParameterBag $post
     * @return bool|JsonResponse
     */
    private function checkParams(array $requiredParams, ParameterBag $post)
    {
        foreach ($requiredParams as $param) {
            if (!$post->has($param))
                return $this->onError('Missing param: ' . $param);
        }
        return true;
    }

    /**
     * @param $message
     * @return JsonResponse
     */
    private function onError($message)
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message
        ]);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    private function onSuccess($data=[])
    {
        if (!array_key_exists('success', $data))
            $data['success'] = true;

        return new JsonResponse($data);
    }
}
