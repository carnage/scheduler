<?php

return [
    'cli_commands' => [
        'factories' => [
            \Carnage\Scheduler\Cli\Command\Wakeup::class =>
                \Carnage\Scheduler\Cli\Command\WakeupFactory::class
        ],
    ],
    'command_handlers' => [
        'factories' => [
            \Carnage\Scheduler\Domain\CommandHandler\Scheduler::class =>
                \Carnage\Scheduler\Service\Factory\CommandHandler\Scheduler::class,
        ],
    ],
    'command_subscriptions' => [
        \Carnage\Scheduler\Domain\Command\ScheduleMessage::class =>
            \Carnage\Scheduler\Domain\CommandHandler\Scheduler::class,
        \Carnage\Scheduler\Domain\Command\WakeUp::class =>
            \Carnage\Scheduler\Domain\CommandHandler\Scheduler::class,
    ],
];
