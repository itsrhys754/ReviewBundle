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

    // src/Repository/ReviewRepository.php

public function findPendingReviews(User $user)
{
    // Using the 'approved' field, which exists on the Review entity
    return $this->createQueryBuilder('r')
        ->where('r.approved = :approved') // filter reviews where approved is false
        ->andWhere('r.user != :user') // optionally, exclude reviews of the current admin
        ->setParameter('approved', false)
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();
}


//    /**
//     * @return Review[] Returns an array of Review objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Review
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
