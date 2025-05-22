<?php

namespace App\Repository;

use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lesson>
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    public function findPaginatedLessons(
        array $criteria = [],
        ?array $order = null,
        int $limit = 10,
        int $page = 1
    ): array {
        $offset = ($page - 1) * $limit;

        return $this->findBy($criteria, $order, $limit, $offset);
    }

    public function countLessonsByCriteria(array $criteria = []): int
    {
        return $this->count($criteria);
    }
}
