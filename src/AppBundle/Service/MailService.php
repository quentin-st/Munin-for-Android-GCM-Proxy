<?php

namespace AppBundle\Service;

use Swift_Mailer;

class MailService
{
	/** @var Swift_Mailer */
	private $mailer;
	/** @var \Twig_Environment */
	private $twig;

	public function __construct($mailer, $twig)
	{
		$this->mailer = $mailer;
		$this->twig = $twig;
	}

	public function sendInstructionsMail($emailAddress, $appId)
	{
		$messageBody = $this->twig->render('AppBundle::instructionsMail.html.twig', [
			'app_id' => $appId
		]);

		$this->sendMail(
			'Notifications install instructions',
			'support@munin-for-android.com',
			'Munin for Android',
			$emailAddress,
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
