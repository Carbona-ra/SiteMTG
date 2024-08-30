<?php

namespace App\Controller;

use App\Entity\CardList;
use App\Form\CardList1Type;
use App\Form\CardListType;
use App\Repository\CardListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/card/list/crud')]
class CardListCrudController extends AbstractController
{
    #[Route('/', name: 'app_card_list_crud_index', methods: ['GET'])]
    public function index(CardListRepository $cardListRepository): Response
    {
        return $this->render('card_list_crud/index.html.twig', [
            'card_lists' => $cardListRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_card_list_crud_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cardList = new CardList();
        $form = $this->createForm(CardListType::class, $cardList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cardList);
            $entityManager->flush();

            return $this->redirectToRoute('app_card_list_crud_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('card_list_crud/new.html.twig', [
            'card_list' => $cardList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_card_list_crud_show', methods: ['GET'])]
    public function show(CardList $cardList): Response
    {
        return $this->render('card_list_crud/show.html.twig', [
            'card_list' => $cardList,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_card_list_crud_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CardList $cardList, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CardListType::class, $cardList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_card_list_crud_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('card_list_crud/edit.html.twig', [
            'card_list' => $cardList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_card_list_crud_delete', methods: ['POST'])]
    public function delete(Request $request, CardList $cardList, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cardList->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cardList);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_card_list_crud_index', [], Response::HTTP_SEE_OTHER);
    }
}
