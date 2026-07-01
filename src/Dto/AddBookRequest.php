<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class AddBookRequest
{
    #[Assert\NotBlank(message: 'Numer seryjny jest wymagany.')]
    #[Assert\Regex(pattern: '/^\d{6}$/', message: 'Numer seryjny musi być sześciocyfrową liczbą.')]
    public ?string $serialNumber = null;

    #[Assert\NotBlank(message: 'Tytuł jest wymagany.')]
    #[Assert\Length(max: 255, maxMessage: 'Tytuł może mieć maksymalnie {{ limit }} znaków.')]
    public ?string $title = null;

    #[Assert\NotBlank(message: 'Autor jest wymagany.')]
    #[Assert\Length(max: 255, maxMessage: 'Autor może mieć maksymalnie {{ limit }} znaków.')]
    public ?string $author = null;
}
