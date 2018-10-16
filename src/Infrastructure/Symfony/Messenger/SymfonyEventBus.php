<?php

namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Event\Event;
use Botilka\Event\EventBus;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyEventBus implements EventBus
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch(Event $message)
    {
        return $this->bus->dispatch($message);
    }
}
