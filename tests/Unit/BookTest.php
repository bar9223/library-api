<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Book;
use App\Exception\BookAlreadyBorrowedException;
use App\Exception\BookNotBorrowedException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BookTest extends TestCase
{
    public function testNewBookIsAvailable(): void
    {
        $book = new Book('100001', 'Lalka', 'Bolesław Prus');

        self::assertFalse($book->isBorrowed());
        self::assertNull($book->getBorrowerCardNumber());
        self::assertNull($book->getBorrowedAt());
    }

    public function testBorrowMarksBookAsBorrowed(): void
    {
        $book = new Book('100001', 'Lalka', 'Bolesław Prus');
        $borrowedAt = new DateTimeImmutable('2026-07-01 10:00:00');

        $book->borrow('654321', $borrowedAt);

        self::assertTrue($book->isBorrowed());
        self::assertSame('654321', $book->getBorrowerCardNumber());
        self::assertSame($borrowedAt, $book->getBorrowedAt());
    }

    public function testBorrowingAnAlreadyBorrowedBookThrows(): void
    {
        $book = new Book('100001', 'Lalka', 'Bolesław Prus');
        $book->borrow('654321', new DateTimeImmutable());

        $this->expectException(BookAlreadyBorrowedException::class);

        $book->borrow('111111', new DateTimeImmutable());
    }

    public function testGiveBackMarksBookAsAvailable(): void
    {
        $book = new Book('100001', 'Lalka', 'Bolesław Prus');
        $book->borrow('654321', new DateTimeImmutable());

        $book->giveBack();

        self::assertFalse($book->isBorrowed());
        self::assertNull($book->getBorrowerCardNumber());
        self::assertNull($book->getBorrowedAt());
    }

    public function testGivingBackAnAvailableBookThrows(): void
    {
        $book = new Book('100001', 'Lalka', 'Bolesław Prus');

        $this->expectException(BookNotBorrowedException::class);

        $book->giveBack();
    }
}
