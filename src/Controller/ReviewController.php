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
}