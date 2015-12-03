<?php

namespace AppBundle\Service;

use AppBundle\Entity\ProxyError;
use Swift_Mailer;

class MailService
{
	/** @var Swift_Mailer */
	private $mailer;
	/** @var \Twig_Environment */
	private $twig;
	private $maintainerEmail;

	private $senderEmail = 'support@munin-for-android.com';
	private $senderName = 'Munin for Android';

	public function __construct($mailer, $twig, $maintainerEmail)
	{
		$this->mailer = $mailer;
		$this->twig = $twig;
		$this->maintainerEmail = $maintainerEmail;
	}

	public function sendInstructionsMail($emailAddress, $appId)
	{
		$messageBody = $this->twig->render('AppBundle::instructionsMail.html.twig', [
			'app_id' => $appId
		]);

		$this->sendMail(
			'Notifications install instructions',
			$this->senderEmail,
			$this->senderName,
			$emailAddress,
			$messageBody
		);
	}

	public function sendProxyExceptionMail(ProxyError $proxyError)
	{
		$messageBody = $this->twig->render('AppBundle::proxyExceptionMail.html.twig', [
			'error' => $proxyError
		]);

		$this->sendMail(
			'New Munin-for-Android-GCM-Proxy error',
			$this->senderEmail,
			$this->senderName,
			$this->maintainerEmail,
			$messageBody
		);
	}

	public function sendMail($subject, $fromMail, $fromName, $to, $messageBody)
	{
		$message = \Swift_Message::newInstance();
		$message
			->setSubject($subject)
			->setFrom([$fromMail => $fromName])
			->setTo($to)
			->setBody($messageBody, 'text/html', 'utf-8');

		$this->mailer->send($message);
	}
}
