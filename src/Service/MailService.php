<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Templating\EngineInterface;

class MailService
{
    private MailerInterface $mailer;
    private EngineInterface $twig;

    private const SENDER_EMAIL = 'support@munin-for-android.com';
    private const SENDER_NAME = 'Munin for Android';

    public function __construct(MailerInterface $mailer, EngineInterface $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendInstructionsMail($emailAddress, $appId): void
    {
        $messageBody = $this->twig->render('/instructions.html.twig', [
            'app_id' => $appId
        ]);

        $this->sendMail(
            'Notifications install instructions',
            $emailAddress,
            $messageBody
        );
    }

    public function sendMail(string $subject, string $to, string $messageBody): void
    {
        $email = (new Email())
            ->from(new Address(self::SENDER_EMAIL, self::SENDER_NAME))
            ->to($to)
            ->subject($subject)
            ->html($messageBody);

        $this->mailer->send($email);
    }
}
