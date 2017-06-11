<?php
namespace Carnage\Scheduler\Domain\Command;

use Carnage\Cqrs\MessageBus\MessageInterface;

interface ScheduleMessage
{
    /**
     * @return MessageInterface
     */
    public function getMessage(): MessageInterface;

    /**
     * @return \DateTimeInterface
     */
    public function getWhen(): \DateTimeImmutable;
}