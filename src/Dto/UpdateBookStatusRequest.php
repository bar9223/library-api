<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateBookStatusRequest
{
    #[Assert\NotNull(message: 'Stan książki jest wymagany.')]
    public ?bool $borrowed = null;

    #[Assert\When(
        expression: 'this.borrowed === true',
        constraints: [
            new Assert\NotBlank(message: 'Numer karty bibliotecznej jest wymagany przy wypożyczeniu.'),
            new Assert\Regex(pattern: '/^\d{6}$/', message: 'Numer karty musi być sześciocyfrową liczbą.'),
        ],
    )]
    public ?string $borrowerCardNumber = null;
}
