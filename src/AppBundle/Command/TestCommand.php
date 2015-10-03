<?php

namespace AppBundle\Command;

use Buzz\Message\MessageInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('gcm:test')
			->setDescription('Sends a test message to a device')
			->addArgument(
				'reg_id',
				InputArgument::REQUIRED,
				'Which device do you want to test?'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$regId = $input->getArgument('reg_id');

		$gcm = $this->getContainer()->get('app.gcm');

		if ($gcm->test([$regId]))
			$output->writeln('Test succeeded.');
		else
			$output->writeln('Test failed.');

		// Write responses
		/** @var MessageInterface $response */
		foreach ($gcm->getClient()->getResponses() as $response)
		{
			$output->writeln($response->getContent());
		}
	}
}
