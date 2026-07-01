<?php

declare(strict_types=1);

namespace App\Controller;

use App\Bus\MessageBusResultTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractApiController extends AbstractController
{
    use MessageBusResultTrait;

    protected MessageBusInterface $commandBus;
    protected MessageBusInterface $queryBus;
    protected SerializerInterface $serializer;

    #[Required]
    public function setBuses(MessageBusInterface $commandBus, MessageBusInterface $queryBus, SerializerInterface $serializer): void
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
        $this->serializer = $serializer;
    }

    protected function dispatchCommand(object $command): mixed
    {
        return $this->dispatchAndGetResult($this->commandBus, $command);
    }

    protected function dispatchQuery(object $query): mixed
    {
        return $this->dispatchAndGetResult($this->queryBus, $query);
    }

    protected function serialized(mixed $data, int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        $json = $this->serializer->serialize($data, 'json', ['groups' => ['book:read']]);

        return new JsonResponse($json, $status, [], true);
    }
}
