<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;

class BaseController extends Controller
{
    /**
     * @param array $requiredParams
     * @param ParameterBag $post
     * @return bool|JsonResponse
     */
    protected function checkParams(array $requiredParams, ParameterBag $post)
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
    protected function onError($message)
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
    protected function onSuccess($data=[])
    {
        if (!array_key_exists('success', $data))
            $data['success'] = true;

        return new JsonResponse($data);
    }
}
