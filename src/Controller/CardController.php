<?php

namespace App\Controller;

use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Deck;
use App\Service\Mtgservice;

#[Route('/card')]
class CardController extends AbstractController
{

    private $mtgService;

    public function __construct(Mtgservice $mtgService)
    {
        $this->mtgService = $mtgService;
    }

    #[Route('/add', name: 'app_card_add', methods: ['POST'])]
    public function addCard(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $deckId = $request->request->get('deck_id');
        $cardName = $request->request->get('card_name');
        $cardImageUrl = $this->mtgService->getCardImage($cardName);

        // Récupérer le deck
        $deck = $entityManager->getRepository(Deck::class)->find($deckId);
        if (!$deck) {
            return new JsonResponse(['status' => 'error', 'message' => 'Deck not found'], 404);
        }

        // Créer la carte
        $card = new Card();
        $card->setName($cardName);
        // Associer la carte au deck
        $card->setAddTo($deck);

        // Enregistrer la carte dans la BDD
        $entityManager->persist($card);
        $entityManager->flush();

        $cardId = $card->getId();

        return new JsonResponse([
            'status' => 'success',
            'card_image_url' => $cardImageUrl,
            'card_id' => $cardId
        ]);
    }

    #[Route('/delete/{id}', name: 'app_card_delete', methods: ['DELETE'])]
    public function deleteCard(int $id, EntityManagerInterface $em): JsonResponse
    {
        $card = $em->getRepository(Card::class)->find($id);

        if (!$card) {
            return new JsonResponse(['status' => 'Card not found'], 404);
        }

        $em->remove($card);
        $em->flush();

        return new JsonResponse(['status' => 'Card deleted']);
    }


}