<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{
	protected function configure()
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

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->getContainer()->get('app.mail_service')->sendInstructionsMail(
			$input->getArgument('mail-address'), '1234');
	}
}
