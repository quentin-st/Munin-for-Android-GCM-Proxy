<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AndroidDevice;
use AppBundle\Entity\MuninMaster;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        if (!$post->has('reg_id'))
            $this->onError('Missing reg_id parameter');


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

        if (!$post->has('reg_id'))
            return $this->onError('Missing reg_id');
        $regId = $post->get('reg_id');

        if (!$post->has('friendly_name'))
            return $this->onError('Missing friendly name');
        $friendlyName = $post->get('friendly_name');

        $em = $this->getDoctrine()->getManager();
        /** @var EntityRepository $deviceRepo */
        $deviceRepo = $em->getRepository('AppBundle:AndroidDevice');

        // Check if already registered
        if ($deviceRepo->findOneBy(['registrationId' => $regId]) != null)
            return $this->onError('This device has already been registered');

        $device = new AndroidDevice();
        $device->setRegistrationId($regId);
        $device->setName($friendlyName);
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

        if (!$post->has('reg_id'))
            return $this->onError('Missing reg_id');
        $regId = $post->get('reg_id');

        $em = $this->getDoctrine()->getManager();
        /** @var EntityRepository $deviceRepo */
        $deviceRepo = $em->getRepository('AppBundle:AndroidDevice');

        /** @var AndroidDevice $device */
        $device = $deviceRepo->findOneBy(['registrationId' => $regId]);
        if (!$device)
            return $this->onError('Unregistered device');

        $master = new MuninMaster();
        $master->setAndroidDevice($device);
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

        if (!$post->has('reg_id'))
            return $this->onError('Missing reg_id');
        $regId = $post->get('reg_id');

        if (!$post->has('hex'))
            return $this->onError('Missing hex');
        $hex = $post->get('hex');

        $em = $this->getDoctrine()->getManager();
        /** @var EntityRepository $deviceRepo */
        $deviceRepo = $em->getRepository('AppBundle:AndroidDevice');
        /** @var EntityRepository $masterRepo */
        $masterRepo = $em->getRepository('AppBundle:MuninMaster');

        /** @var AndroidDevice $device */
        $device = $deviceRepo->findOneBy(['registrationId' => $regId]);
        if (!$device)
            return $this->onError('Unregistered device');

        // Find master
        /** @var MuninMaster $master */
        $master = $masterRepo->findOneBy(['hex' => $hex]);
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
