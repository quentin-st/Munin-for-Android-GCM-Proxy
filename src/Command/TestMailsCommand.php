<?php

namespace App\Command;

use App\Service\MailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestMailsCommand extends Command
{
    private MailService $mailService;

    public function __construct(MailService $mailService)
    {
        parent::__construct();

        $this->mailService = $mailService;
    }

    protected function configure(): void
	{
		$this
			->setName('gcm:test-mails')
			->setDescription('Sends a test mail to a mail address')
			->addArgument(
				'mail-address',
				InputArgument::REQUIRED,
				'Which address do you want to test?'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->mailService->sendInstructionsMail(
			$input->getArgument('mail-address'),
            '1234'
        );

		return 0;
	}
}
