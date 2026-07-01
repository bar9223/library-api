<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class BookNotBorrowedException extends DomainException
{
    public function __construct(string $serialNumber)
    {
        parent::__construct(sprintf('Książka %s nie jest obecnie wypożyczona.', $serialNumber));
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
