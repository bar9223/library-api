<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function save(Book $book): void
    {
        $this->getEntityManager()->persist($book);
        $this->getEntityManager()->flush();
    }

    public function remove(Book $book): void
    {
        $this->getEntityManager()->remove($book);
        $this->getEntityManager()->flush();
    }

    public function paginate(?string $search, int $page, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('b')->orderBy('b.id', 'ASC');

        if ($search !== null && $search !== '') {
            $queryBuilder
                ->andWhere('LOWER(b.title) LIKE :term OR LOWER(b.author) LIKE :term OR b.serialNumber LIKE :term')
                ->setParameter('term', '%'.mb_strtolower($search).'%');
        }

        $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($queryBuilder, false);

        return [
            'items' => iterator_to_array($paginator),
            'total' => count($paginator),
        ];
    }

    public function existsBySerialNumber(string $serialNumber): bool
    {
        return (bool) $this->count(['serialNumber' => $serialNumber]);
    }
}
