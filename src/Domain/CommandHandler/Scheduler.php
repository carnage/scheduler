<?php

namespace Carnage\Scheduler\Domain\CommandHandler;

use Carnage\Cqrs\Event\DomainMessage;
use Carnage\Cqrs\MessageBus\MessageInterface;
use Carnage\Cqrs\MessageHandler\MessageHandlerInterface;
use Carnage\Cqrs\Persistence\EventStore\NotFoundException;
use Carnage\Cqrs\Persistence\Repository\RepositoryInterface;
use Carnage\Scheduler\Domain\Command\ScheduleMessage;
use Carnage\Scheduler\Domain\Command\WakeUp;
use Carnage\Scheduler\Domain\Model\Scheduler as SchedulerModel;

class Scheduler implements MessageHandlerInterface
{
    private $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function handleDomainMessage(DomainMessage $message)
    {
        $this->handle($message->getEvent());
    }

    public function handle(MessageInterface $message)
    {
        if ($message instanceof ScheduleMessage) {
            $this->handleScheduleMessage($message);
        }

        if ($message instanceof WakeUp) {
            $this->handleWakeUp($message);
        }
    }

    protected function handleScheduleMessage(ScheduleMessage $command)
    {
        $alarmClock = $this->loadScheduler(SchedulerModel::makeId($command->getWhen()));
        $alarmClock->sendAt($command->getMessage(), $command->getWhen());
        $this->repository->save($alarmClock);
    }

    protected function handleWakeUp(WakeUp $command)
    {
        $alarmClock = $this->loadScheduler(SchedulerModel::makeId(new \DateTime()));
        $alarmClock->wakeUp();
        $this->repository->save($alarmClock);
    }

    private function loadScheduler($id): SchedulerModel
    {
        try {
            $process = $this->repository->load($id);
        } catch (NotFoundException $e) {
            $process = new SchedulerModel();
        }

        return $process;
    }
}
