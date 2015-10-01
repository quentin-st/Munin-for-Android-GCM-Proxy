<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AndroidDevice;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/declareAlert", name="declareAlert")
     * @Method({"POST"})
     */
    public function declareAlertAction(Request $request)
    {
        // Check POST params
        $post = $request->request;

        if (!$post->has('reg_id'))
            $this->onError('Missing reg_id parameter');


        return new JsonResponse();
    }

    /**
     * @Route("/register", name="register")
     * @Method({"POST"})
     */
    public function registerDeviceAction(Request $request)
    {
        $post = $request->request;

        if (!$post->has('reg_id'))
            return $this->onError('Missing reg_id');
        $regId = $post->get('reg_id');

        $em = $this->getDoctrine()->getManager();
        $deviceRepo = $em->getRepository('AppBundle:AndroidDevice');

        // Check if already registered
        if ($deviceRepo->findOneBy(['registrationId' => $regId]) != null)
            return $this->onError('This device has already been registered');

        $device = new AndroidDevice();
        $device->setRegistrationId($regId);
        $em->persist($device);
        $em->flush();

        return $this->onSuccess();
    }

    private function onError($message)
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message
        ]);
    }

    private function onSuccess($data=[])
    {
        if (!array_key_exists('success', $data))
            $data['success'] = true;

        return new JsonResponse($data);
    }
}
