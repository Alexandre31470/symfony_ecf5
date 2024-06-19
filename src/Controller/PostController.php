<?php

// src/Controller/PostController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface; // Importe EntityManagerInterface pour gérer l'entité
use App\Form\PostType;
use Symfony\Component\HttpFoundation\Request;

class PostController extends AbstractController
{
    private EntityManagerInterface $entityManager; // Déclare une propriété pour stocker l'EntityManager

    // Injecte automatiquement l'EntityManager via l'injection de dépendance dans le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/post', name: 'post_index')]
    public function index(): Response
    {
        // Récupère tous les articles depuis la base de données
        $posts = $this->entityManager->getRepository(Post::class)->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/new', name: 'post_new')]
    public function new(Request $request): Response
    {
        // Crée une nouvelle instance de Post
        $post = new Post();

        // Crée un formulaire basé sur le PostType avec l'objet Post
        $form = $this->createForm(PostType::class, $post);

        // Gère la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Persiste l'entité Post en base de données
            $this->entityManager->persist($post);
            $this->entityManager->flush();

            // Redirige vers la page d'index des articles après soumission réussie du formulaire
            return $this->redirectToRoute('post_index');
        }

        // Rend la vue du formulaire avec le formulaire créé
        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/{id}/edit', name: 'post_edit')]
    public function edit(Post $post, Request $request): Response
    {
        // Crée un formulaire basé sur le PostType avec l'objet Post existant
        $form = $this->createForm(PostType::class, $post);

        // Gère la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Persiste les changements en base de données
            $this->entityManager->flush();

            // Redirige vers la page d'index des articles après soumission réussie du formulaire
            return $this->redirectToRoute('post_index');
        }

        // Rend la vue du formulaire d'édition avec le formulaire créé
        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post, // Assurez-vous que 'post' est bien transmis
        ]);
    }

    #[Route('/post/delete/{id}', name: 'post_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/delete.html.twig', [
            'post' => $post,
        ]);
    }}