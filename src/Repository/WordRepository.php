<?php

namespace App\Repository;

use App\Entity\Word;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Word>
 */
class WordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Word::class);
    }

    /**
     * @param int|\App\Repository\Lesson $lesson
     * @return Word[]
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function findByLessonId(int|Lesson $lesson): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.lesson = :lesson')
            ->setParameter('lesson', $lesson)
            ->indexBy('w', 'w.id')
            ->getQuery()
            ->getResult();
    }
}
