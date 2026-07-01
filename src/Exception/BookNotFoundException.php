<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class BookNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(sprintf('Nie znaleziono książki o identyfikatorze %d.', $id));
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
