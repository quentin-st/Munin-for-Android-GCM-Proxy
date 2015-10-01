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
		$this->sendMail(
			'Notifications install instructions',
			'support@munin-for-android.com',
			'Munin for Android',
			$emailAddress,
			'Here is your app id: ' . $appId
		);
	}

	public function sendMail($subject, $fromMail, $fromName, $to, $text, $plainText=null)
	{
		$messageBody = $this->twig->render('AppBundle::mailBody.html.twig', [
			'title' => $subject,
			'content' => $text
		]);

		$message = \Swift_Message::newInstance();
		$message
			->setSubject($subject)
			->setFrom([$fromMail => $fromName])
			->setTo($to)
			->setBody($messageBody, 'text/html', 'utf-8');

		if ($plainText != null)
			$message->addPart($plainText, 'text/plain');

		$this->mailer->send($message);
	}
}
