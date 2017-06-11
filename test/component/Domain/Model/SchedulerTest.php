<?php

namespace Carnage\Scheduler\Domain\Model;

use Carnage\Cqrs\Testing\AbstractBusTest;
use Carnage\Cqrs\MessageBus\MessageInterface;
use Carnage\Scheduler\Domain\Event\MessageScheduled;
use Carnage\Scheduler\Domain\Command\ScheduleMessage;
use Carnage\Scheduler\Domain\Command\WakeUp;
use Carnage\Scheduler\Domain\CommandHandler\Scheduler as SchedulerCommandHandler;

class SchedulerTest extends AbstractBusTest
{
    protected $modelClass = Scheduler::class;

    public function test_it_capture_the_delayed_message()
    {
        $command = new class implements ScheduleMessage, MessageInterface {
            public function getWhen(): \DateTimeImmutable {return new \DateTimeImmutable();}
            public function getMessage(): MessageInterface {return new WakeUp();}
        };

        $sut = new SchedulerCommandHandler($this->repository);

        $sut->handle($command);

        $event = $this->messageBus->messages[0]->getEvent();
        self::assertInstanceOf(MessageScheduled::class, $event);
    }

    public function test_it_sends_the_delayed_message()
    {
        $when = new \DateTimeImmutable();
        $deferredMessage = new WakeUp();
        $this->given(Scheduler::class, Scheduler::makeId($when), [new MessageScheduled($deferredMessage, $when)]);

        $sut = new SchedulerCommandHandler($this->repository);

        $command = new WakeUp();
        $sut->handle($command);

        $event = $this->messageBus->messages[0]->getEvent();
        self::assertSame($deferredMessage, $event);
    }

    public function test_it_doesnt_send_future_messages()
    {
        $when = (new \DateTimeImmutable())->add(new \DateInterval('PT1M'));
        $deferredMessage = new WakeUp();
        $this->given(Scheduler::class, Scheduler::makeId($when), [new MessageScheduled($deferredMessage, $when)]);

        $sut = new SchedulerCommandHandler($this->repository);

        $command = new WakeUp();
        $sut->handle($command);

        self::assertEmpty($this->messageBus->messages);
    }

    public function test_it_doesnt_send_messages_if_there_are_none()
    {
        $sut = new SchedulerCommandHandler($this->repository);

        $command = new WakeUp();
        $sut->handle($command);

        self::assertEmpty($this->messageBus->messages);
    }
}