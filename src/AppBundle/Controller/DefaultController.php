<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AndroidDevice;
use AppBundle\Entity\MuninMaster;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/munin/declareAlert", name="declareAlert")
     * @Method({"POST"})
     */
    public function declareAlertAction(Request $request)
    {
        // Check POST params
        $post = $request->request;

        $check = $this->checkParams(['reg_id'], $post);
        if ($check !== true)
            return $check;


        return new JsonResponse();
    }

    /**
     * Called from Android device: registers this device
     * @Route("/android/register", name="register")
     * @Method({"POST"})
     */
    public function registerDeviceAction(Request $request)
    {
        $post = $request->request;

        $check = $this->checkParams(['reg_id', 'friendly_name'], $post);
        if ($check !== true)
            return $check;

        $em = $this->getDoctrine()->getManager();
        /** @var EntityRepository $deviceRepo */
        $deviceRepo = $em->getRepository('AppBundle:AndroidDevice');

        // Check if already registered
        if ($deviceRepo->findOneBy(['registrationId' => $post->get('reg_id')]) != null)
            return $this->onError('This device has already been registered');

        $device = new AndroidDevice();
        $device->setRegistrationId($post->get('reg_id'));
        $device->setName($post->get('friendly_name'));
        $em->persist($device);
        $em->flush();

        return $this->onSuccess();
    }

    /**
     * Called from Android device: add a server
     * @Route("/android/addMaster")
     * @Method({"POST"})
     */
    public function addMaster(Request $request)
    {
        $post = $request->request;

        $check = $this->checkParams(['reg_id', 'mfa_id'], $post);
        if ($check !== true)
            return $check;

        $em = $this->getDoctrine()->getManager();
        /** @var EntityRepository $deviceRepo */
        $deviceRepo = $em->getRepository('AppBundle:AndroidDevice');

        /** @var AndroidDevice $device */
        $device = $deviceRepo->findOneBy(['registrationId' => $post->get('reg_id')]);
        if (!$device)
            return $this->onError('Unregistered device');

        $master = new MuninMaster();
        $master->setAndroidDevice($device);
        $master->setMfaId($post->get('mfa_id'));
        $device->addMaster($master);
        $em->persist($master);
        $em->flush();

        return $this->onSuccess([
            'hex' => $master->getHex(),
            'config' => $this->get('app.config')->generateConfig($master)
        ]);
    }

    /**
     * Called from Android device: remove a server
     * @Route("/android/removeMaster")
     * @Method({"POST"})
     */
    public function removeMaster(Request $request)
    {
        $post = $request->request;

        $check = $this->checkParams(['reg_id', 'hex'], $post);
        if ($check !== true)
            return $check;


        $em = $this->getDoctrine()->getManager();
        /** @var EntityRepository $deviceRepo */
        $deviceRepo = $em->getRepository('AppBundle:AndroidDevice');
        /** @var EntityRepository $masterRepo */
        $masterRepo = $em->getRepository('AppBundle:MuninMaster');

        /** @var AndroidDevice $device */
        $device = $deviceRepo->findOneBy(['registrationId' => $post->get('reg_id')]);
        if (!$device)
            return $this->onError('Unregistered device');

        // Find master
        /** @var MuninMaster $master */
        $master = $masterRepo->findOneBy(['hex' => $post->get('hex')]);
        if (!$master)
            return $this->onError('Unknown master');

        if (!$device->getMasters()->contains($master))
            return $this->onError('This master does not belong to this device');

        // Remove it
        $device->removeMaster($master);
        $em->remove($master);
        $em->flush();

        return $this->onSuccess();
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
