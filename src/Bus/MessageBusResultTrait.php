<?php

declare(strict_types=1);

namespace App\Bus;

use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

trait MessageBusResultTrait
{
    private function dispatchAndGetResult(MessageBusInterface $bus, object $message): mixed
    {
        $envelope = $bus->dispatch($message);

        $handledStamps = $envelope->all(HandledStamp::class);

        if ($handledStamps === []) {
            throw new LogicException(sprintf('Message of type "%s" was handled zero times.', $message::class));
        }

        return $handledStamps[0]->getResult();
    }
}
