<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\EpisodeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


#[Route('/comment', name: 'comment_')]
class CommentController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

    #[Route('/new/{episodeId}', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_CONTRIBUTOR")]
    public function new(Request $request, CommentRepository $commentRepository, EpisodeRepository $episodeRepository, int $episodeId): Response
    {
        $comment = new Comment();
        $episode = $episodeRepository->find($episodeId);
        if ($episode === null) {
            throw $this->createNotFoundException('No episode found for id ' . $episodeId);
        }
        $comment->setEpisode($episode);
        $comment->setAuthor($this->getUser());
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $commentRepository->save($comment, true);
            $this->addFlash('success', 'Comment created successfully!');
            return $this->redirectToRoute('comment_show', ['id' => $episode->getId()]);
        }

        return $this->render('comment/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Episode $episode, CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findBy(
            ['episode' => $episode],
            ['createdAt' => 'ASC']
        );

        return $this->render('comment/show.html.twig', [
            'comments' => $comments,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    #[IsGranted("ROLE_CONTRIBUTOR")]
    public function delete(Request $request, Comment $comment, CommentRepository $commentRepository, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        $episode = $comment->getEpisode();
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            if ($authorizationChecker->isGranted('ROLE_ADMIN') || $comment->getAuthor() === $this->getUser()) {
                $commentRepository->remove($comment, true);
                $this->addFlash('danger', 'Comment deleted successfully!');
            } else {
                $this->addFlash('danger', 'You are not allowed to delete this comment!');
            }
        }

        return $this->redirectToRoute('comment_show', ['id' => $episode->getId()]);
    }




}