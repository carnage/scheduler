<?php

namespace Carnage\Scheduler\Domain\Event;

use JMS\Serializer\Annotation as JMS;
use Carnage\Cqrs\Event\EventInterface;
use Carnage\Cqrs\MessageBus\MessageInterface;

class MessageScheduled implements EventInterface
{
    /**
     * @JMS\Type("Object")
     * @var MessageInterface
     */
    private $message;

    /**
     * @JMS\Type("DateTimeImmutable")
     * @var \DateTimeImmutable
     */
    private $when;

    /**
     * MessageScheduled constructor.
     * @param MessageInterface $message
     * @param \DateTimeImmutable $when
     */
    public function __construct(MessageInterface $message, \DateTimeImmutable $when)
    {
        $this->message = $message;
        $this->when = $when;
    }

    /**
     * @return MessageInterface
     */
    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getWhen(): \DateTimeImmutable
    {
        return $this->when;
    }
}
