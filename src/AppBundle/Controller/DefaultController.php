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
        foreach ($array['alert'] as $alert)
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
                    foreach ($alert[$level] as $field)
                    {
                        $a->addField(new Field(
                            $field['@attributes']['label'],
                            $field['@attributes']['value'],
                            $field['@attributes']['w'],
                            $field['@attributes']['c'],
                            $field['@attributes']['extra'],
                            Level::fromLabel($level)
                        ));
                    }
                }
            }

            $alerts[] = $a;
        }

        // Notify each alert
        $gcmService = $this->get('app.gcm');
        foreach ($alerts as $alert)
        {
            $gcmService->notifyAlert($reg_ids, $alert);
        }

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
