<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\DomainException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $throwable = $this->unwrap($event->getThrowable());

        $event->setResponse($this->buildResponse($throwable));
    }

    private function unwrap(Throwable $throwable): Throwable
    {
        while ($throwable instanceof HandlerFailedException && $throwable->getPrevious() !== null) {
            $throwable = $throwable->getPrevious();
        }

        return $throwable;
    }

    private function buildResponse(Throwable $throwable): JsonResponse
    {
        $validation = $this->extractValidationFailure($throwable);

        if ($validation !== null) {
            return new JsonResponse(
                [
                    'error' => 'Błąd walidacji.',
                    'violations' => $this->formatViolations($validation),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        if ($throwable instanceof DomainException) {
            return new JsonResponse(['error' => $throwable->getMessage()], $throwable->getStatusCode());
        }

        if ($throwable instanceof HttpExceptionInterface) {
            return new JsonResponse(['error' => $throwable->getMessage()], $throwable->getStatusCode());
        }

        return new JsonResponse(['error' => 'Wewnętrzny błąd serwera.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function extractValidationFailure(Throwable $throwable): ?ValidationFailedException
    {
        if ($throwable instanceof ValidationFailedException) {
            return $throwable;
        }

        $previous = $throwable->getPrevious();

        return $previous instanceof ValidationFailedException ? $previous : null;
    }

    private function formatViolations(ValidationFailedException $exception): array
    {
        $violations = [];

        foreach ($exception->getViolations() as $violation) {
            $violations[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return $violations;
    }
}
