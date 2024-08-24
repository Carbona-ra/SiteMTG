<?php

namespace App\Controller;

use App\Entity\CardList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Deck;

#[Route('/card/list')]
class CardListController extends AbstractController
{


#[Route('/add', name: 'app_card_add', methods: ['POST'])]
    public function addCard(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $deckId = $request->request->get('deck_id');
        $cardName = $request->request->get('card_name');

        // Récupérer le deck
        $deck = $entityManager->getRepository(Deck::class)->find($deckId);
        if (!$deck) {
            return new JsonResponse(['status' => 'error', 'message' => 'Deck not found'], 404);
        }

        // Créer la carte
        $card = new CardList();
        $card->setName($cardName);
        // Associer la carte au deck
        $card->setAddTo($deck);

        // Enregistrer la carte dans la BDD
        $entityManager->persist($card);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success', 'card_id' => $card->getId()]);
    }

    #[Route('/remove', name: 'app_card_remove', methods: ['POST'])]
    public function removeCard(Request $request, EntityManagerInterface $entityManager): Response
    {
        $deckId = $request->request->get('deck_id');
        $cardId = $request->request->get('card_id');
        
        return new JsonResponse(['status' => 'success']);
    }
}