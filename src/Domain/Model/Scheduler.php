<?php

namespace Carnage\Scheduler\Domain\Model;

use Carnage\Cqrs\Aggregate\AggregateInterface;
use Carnage\Cqrs\Event\DomainMessage;
use Carnage\Cqrs\MessageBus\MessageInterface;
use Carnage\Scheduler\Domain\Event\MessageScheduled;

class Scheduler implements AggregateInterface
{
    private $id;
    private $queue;
    private $uncommittedEvents = [];
    private $version = 0;

    public function __construct()
    {
        $this->queue = new \SplPriorityQueue();
    }

    public function getId()
    {
        return $this->id;
    }

    public function sendAt(MessageInterface $message, \DateTimeImmutable $when)
    {
        if ($this->id !== null) {
            $this->validateBucket($when);
        }

        $event = new MessageScheduled($message, $when);

        $this->apply($event);
    }

    protected function applyMessageScheduled(MessageScheduled $event)
    {
        $this->id = self::makeId($event->getWhen());
        $this->queue->insert($event, $this->makePriority($event->getWhen()));
    }

    public function wakeUp()
    {
        if ($this->queue->isEmpty()) {
            return;
        }

        /** @var SendAt $nextMessage */
        $nextMessage = $this->queue->top();
        if ($nextMessage->getWhen() <= new \DateTimeImmutable()) {
            $this->apply($nextMessage->getMessage());
        }
    }

    protected function unknownMessage(MessageInterface $message)
    {
        $this->queue->extract();
    }

    private function validateBucket(\DateTimeInterface $time)
    {
        if (self::makeId($time) !== $this->id) {
            throw new \DomainException('Cannot add this message to this instance');
        }
    }

    public static function makeId(\DateTimeInterface $time): string
    {
        return $time->format('Ymd');
    }

    /**
     * Priority is in order based on the hours, mins + seconds; It is then made negative, so that earlier times go to
     * the top of the priority queue.
     *
     * (G = no leading 0)
     */
    private function makePriority(\DateTimeInterface $time): int
    {
        return 0 - ((int) $time->format('Gis'));
    }

    private function getApplyMethod(MessageInterface $event)
    {
        $classParts = explode('\\', get_class($event));
        return 'apply' . end($classParts);
    }

    /**
     * @param DomainMessage[] $events
     * @return static
     */
    public static function fromEvents(DomainMessage ...$events)
    {
        $instance = new static();

        foreach ($events as $event) {
            $instance->apply($event->getEvent(), false);
        }

        return $instance;
    }

    public function apply(MessageInterface $event, $new = true)
    {
        $this->version++;

        $method = $this->getApplyMethod($event);

        if (method_exists($this, $method)) {
            $this->$method($event);
        } else {
            $this->unknownMessage($event);
        }

        if ($new) {
            $this->uncommittedEvents[$this->version] = DomainMessage::recordEvent(
                static::class,
                $this->getId(),
                $this->version,
                $event
            );
        }
    }

    public function getUncommittedEvents()
    {
        return $this->uncommittedEvents;
    }

    public function committed()
    {
        $this->uncommittedEvents = [];
    }

    public function getVersion()
    {
        return $this->version;
    }
}