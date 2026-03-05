<?php

namespace App\Repository;

use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $page
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getPaginatedLessons(
        array $criteria = [],
        array $orderBy = ['id' => 'DESC'],
        int $limit = 10,
        int $page = 1
    ): Paginator {
        $query = $this->createQueryBuilder('l');

        foreach ($criteria as $field => $value) {
            $query->andWhere(sprintf('l.%s = :%s', $field, $field))
                ->setParameter($field, $value);
        }

        foreach ($orderBy as $field => $direction) {
            $query->addOrderBy(sprintf('l.%s', $field), $direction);
        }

        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }
}
