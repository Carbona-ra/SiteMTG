<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Form\DeckSearchType;
use App\Form\DeckType;
use App\Repository\DeckRepository;
use App\Service\Mtgservice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;


class IndexController extends AbstractController
{

    public function __construct(Mtgservice $mtgService)
    {
        $this->mtgService = $mtgService;
    }

    #[Route('/', name: 'homepage')]
    public function index(DeckRepository $deckRepository, Security $security): Response
    {
        $user = $security->getUser();
        
        // Récupérer les decks de l'utilisateur connecté
        $userDecks = $user ? $deckRepository->findBy(['Creator' => $user]) : [];
        
        // Récupérer tous les decks
        $allDecks = $deckRepository->findAll();

        // Initialiser le service Filesystem
        $filesystem = new Filesystem();

        // Grouper les decks par créateur et vérifier l'existence de l'image
        $groupedDecks = [];
        foreach ($allDecks as $deck) {
            $creator = $deck->getCreator();
            if (!isset($groupedDecks[$creator->getId()])) {
                $groupedDecks[$creator->getId()] = [
                    'creator' => $creator,
                    'decks' => [],
                ];
            }

            // Vérifier si le fichier de l'image de bannière existe
            $imagePath = $this->getParameter('deck_images_directory') . '/' . $deck->getImageName();
            $deck->bannerExists = $filesystem->exists($imagePath);

            $groupedDecks[$creator->getId()]['decks'][] = $deck;
        }

        // Vérifier l'existence de l'image pour les decks de l'utilisateur connecté
        foreach ($userDecks as $deck) {
            $imagePath = $this->getParameter('deck_images_directory') . '/' . $deck->getImageName();
            $deck->bannerExists = $filesystem->exists($imagePath);
        }

        return $this->render('deck/index.html.twig', [
            'user_decks' => $userDecks,
            'grouped_decks' => $groupedDecks,
        ]);
    }
}
