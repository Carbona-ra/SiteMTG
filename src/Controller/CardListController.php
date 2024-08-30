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
use App\Service\Mtgservice;

#[Route('/card')]
class CardListController extends AbstractController
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
        $card = new CardList();
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

    #[Route('/delete/{id}"', name: 'app_card_delete', methods: ['DELETE'])]
    public function deleteCard(int $id, EntityManagerInterface $em): JsonResponse
    {
        $card = $em->getRepository(CardList::class)->find($id);

        if (!$card) {
            return new JsonResponse(['status' => 'Card not found'], 404);
        }

        $em->remove($card);
        $em->flush();

        return new JsonResponse(['status' => 'Card deleted']);
    }

    // test pr plustard (venant du crud auto)
    // #[Route('/testDelete/{id}', name: 'app_card_list_crud_delete', methods: ['POST'])]
    // public function delete(Request $request, CardList $cardList, EntityManagerInterface $entityManager): Response
    // {
    //     if ($this->isCsrfTokenValid('delete'.$cardList->getId(), $request->getPayload()->getString('_token'))) {
    //         $entityManager->remove($cardList);
    //         $entityManager->flush();
    //     }

    //     return $this->redirectToRoute('app_card_list_crud_index', [], Response::HTTP_SEE_OTHER);
    // }

}