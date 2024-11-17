<?php

namespace Rhys\ReviewBundle\Repository;

use Rhys\ReviewBundle\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\Book;


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

    /**
     * Find reviews for a specific book, optionally sorted by a specified criteria.
     * 
     * @param Book $book The book for which to find reviews.
     * @param string $sort The sorting criteria (e.g., 'recent', 'lowest', 'highest').
     * @return Review[] Returns an array of Review objects.
     */
    public function findFilteredReviews(Book $book, string $sort): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.book = :book')
            ->setParameter('book', $book);

        // Apply sorting based on the provided criteria
        switch ($sort) {
            case 'lowest':
                $qb->orderBy('r.rating', 'ASC');
                break;
            case 'highest':
                $qb->orderBy('r.rating', 'DESC');
                break;
            case 'recent':
            default:
                $qb->orderBy('r.createdAt', 'DESC');
                break;
        }

        return $qb->getQuery()->getResult();
    }
}
