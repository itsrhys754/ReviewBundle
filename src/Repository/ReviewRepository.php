<?php

namespace Rhys\ReviewBundle\Repository;

use Rhys\ReviewBundle\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;


/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * Find pending reviews that are not yet approved.
     * Optionally, exclude reviews submitted by a specific user.
     * This method returns an array of reviews that are pending approval.
     */
    public function findPendingReviews(?User $excludeUser = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.approved = :approved')
            ->setParameter('approved', false);
        
        if ($excludeUser) {
            $qb->andWhere('r.user != :user')
               ->setParameter('user', $excludeUser);
        }
        
        return $qb->getQuery()->getResult();
    }
}
