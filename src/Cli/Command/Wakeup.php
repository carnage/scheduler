<?php

namespace Carnage\Scheduler\Cli\Command;

use Carnage\Cqrs\MessageBus\MessageBusInterface;
use Carnage\Scheduler\Domain\Command\WakeUp as WakeUpCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Wakeup extends Command
{
    /**
     * @var MessageBusInterface
     */
    private $commandBus;

    public static function build(MessageBusInterface $commandBus)
    {
        $instance = new static();
        $instance->commandBus = $commandBus;

        return $instance;
    }

    protected function configure()
    {
        $this->setName('cqrs:scheduler:wakeup')
            ->setDescription('Delivers any messages which have passed their schedule time');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandBus->dispatch(new WakeUpCommand());
    }
}
