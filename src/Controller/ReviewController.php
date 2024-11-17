<?php

namespace Rhys\ReviewBundle\Controller;

use App\Entity\Book;
use Rhys\ReviewBundle\Entity\Review;
use Rhys\ReviewBundle\Form\ReviewType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Rhys\ReviewBundle\Entity\Vote;

class ReviewController extends AbstractController
{
    #[Route('/book/{bookId}/review/new', name: 'app_review_new')]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager, 
        int $bookId
    ): Response {
        $book = $entityManager->getRepository(Book::class)->find($bookId);
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $review = new Review();
        $review->setApproved(false);
        $form = $this->createForm(ReviewType::class, $review);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $review->setBook($book);
                $review->setUser($this->getUser());
                $review->setCreatedAt(new \DateTime());
                
                $entityManager->persist($review);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Your review has been submitted and is pending approval.'
                );
            } catch (\Exception $e) {
                $this->addFlash(
                    'error',
                    'There was an error submitting your review. Please try again.'
                );
            }

            return $this->redirectToRoute('app_book_show', ['id' => $bookId]);
        }

        return $this->render('@Review/review/form.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
            'editing' => false
        ]);
    }

    #[Route('/review/{id}/edit', name: 'app_review_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        Review $review
    ): Response {
        // Check if the current user is the owner of the review
        if ($review->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit your own reviews.');
        }

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Reset approval status when edited
                $review->setApproved(false);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Your review has been updated and is pending approval.'
                );
            } catch (\Exception $e) {
                $this->addFlash(
                    'error',
                    'There was an error updating your review. Please try again.'
                );
            }

            return $this->redirectToRoute('app_book_show', ['id' => $review->getBook()->getId()]);
        }

        return $this->render('@Review/review/form.html.twig', [
            'form' => $form->createView(),
            'book' => $review->getBook(),
            'editing' => true
        ]);
    }

    #[Route('/book/{id}/reviews', name: 'app_book_reviews')]
    public function getFilteredReviews(
        Request $request,
        EntityManagerInterface $entityManager,
        Book $book
    ): Response {
        $sort = $request->query->get('sort', 'recent'); // Default to recent
        
        $reviews = $entityManager->getRepository(Review::class)
            ->findFilteredReviews($book, $sort);

        return $this->json([
            'reviews' => array_map(function($review) {
                return [
                    'id' => $review->getId(),
                    'content' => $review->getContent(),
                    'upvotes' => $review->getUpvotesCount(),
                    'downvotes' => $review->getDownvotesCount(),
                    'rating' => $review->getRating(),
                    'createdAt' => $review->getCreatedAt()->format('M d, Y'),
                    'username' => $review->getUser()->getUsername(),
                    'userInitial' => substr($review->getUser()->getUsername(), 0, 1),
                    'containsSpoilers' => $review->isContainsSpoilers(),
                    'approved' => $review->isApproved(),
                ];
            }, $reviews)
        ]);
    }

    #[Route('/review/{id}/vote', name: 'app_review_vote')]
    public function vote(
        Review $review,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $voteType = $request->query->get('type'); // upvote or downvote
        $user = $this->getUser();

        // Check if the user has already voted
        $existingVote = $entityManager->getRepository(Vote::class)->findOneBy([
            'review' => $review,
            'user' => $user,
        ]);

        // If the user has already voted, remove the vote and update with the new vote
        if ($existingVote) {
            $entityManager->remove($existingVote);
            $entityManager->flush();
        }

        // Create a new vote
        $vote = new Vote();
        $vote->setReview($review);
        $vote->setUser($user);
        $vote->setType($voteType); 

        if ($voteType === 'upvote') {
            $review->addVote($vote);
        } elseif ($voteType === 'downvote') {
            $review->addVote($vote);
        }

        $entityManager->persist($vote); // Persist the new vote
        $entityManager->flush();

        return $this->json([
            'upvotes' => $review->getUpvotesCount(),
            'downvotes' => $review->getDownvotesCount(),
        ]);
    }
}
