<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\BookAlreadyBorrowedException;
use App\Exception\BookNotBorrowedException;
use App\Repository\BookRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table(name: 'book')]
#[ORM\UniqueConstraint(name: 'uniq_book_serial_number', columns: ['serial_number'])]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['book:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 6, unique: true)]
    #[Groups(['book:read'])]
    private string $serialNumber;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['book:read'])]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['book:read'])]
    private string $author;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['book:read'])]
    private bool $borrowed = false;

    #[ORM\Column(type: Types::STRING, length: 6, nullable: true)]
    #[Groups(['book:read'])]
    private ?string $borrowerCardNumber = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['book:read'])]
    private ?DateTimeImmutable $borrowedAt = null;

    public function __construct(string $serialNumber, string $title, string $author)
    {
        $this->serialNumber = $serialNumber;
        $this->title = $title;
        $this->author = $author;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function isBorrowed(): bool
    {
        return $this->borrowed;
    }

    public function getBorrowerCardNumber(): ?string
    {
        return $this->borrowerCardNumber;
    }

    public function getBorrowedAt(): ?DateTimeImmutable
    {
        return $this->borrowedAt;
    }

    public function borrow(string $borrowerCardNumber, DateTimeImmutable $borrowedAt): void
    {
        if ($this->borrowed) {
            throw new BookAlreadyBorrowedException($this->serialNumber);
        }

        $this->borrowed = true;
        $this->borrowerCardNumber = $borrowerCardNumber;
        $this->borrowedAt = $borrowedAt;
    }

    public function giveBack(): void
    {
        if (!$this->borrowed) {
            throw new BookNotBorrowedException($this->serialNumber);
        }

        $this->borrowed = false;
        $this->borrowerCardNumber = null;
        $this->borrowedAt = null;
    }
}
