<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class DuplicateSerialNumberException extends DomainException
{
    public function __construct(string $serialNumber)
    {
        parent::__construct(sprintf('Książka o numerze seryjnym %s już istnieje.', $serialNumber));
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
