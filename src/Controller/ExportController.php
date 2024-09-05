<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Repository\DeckRepository;
use App\Service\DeckTextFileGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

class ExportController extends AbstractController
{
    
    private DeckTextFileGenerator $generator;

    public function __construct(DeckTextFileGenerator $generator)
    {
        $this->generator = $generator;
    }

    #[Route('/export', name: 'app_export')]
    public function downloadDeck(DeckRepository $deckRepository, Security $security): Response
    {
        $user = $security->getUser();

        // vérif si l'utilisateur est connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à vos decks.');
        }

        // récupérer les decks du mec connecté
        $decks = $deckRepository->findBy(['Creator' => $user]); 

        $deckFiles = [];

        foreach ($decks as $deck) {
            $fileName = sprintf('deck_%s.txt', preg_replace('/[^a-zA-Z0-9_-]/', '_', $deck->getName()));
            $filePath = sprintf('fichiertxt/%s', $fileName);
            $this->generator->saveToFile($deck, $filePath);
        
            if (file_exists($filePath)) {
                $deckFiles[] = $fileName; 
            }
        }

        // renvoyer les fichiers à la vue
        return $this->render('export/index.html.twig', [
            'decks' => $decks,
            'deckFiles' => $deckFiles,
        ]);
    }

    #[Route('/export/{fileName}', name: 'app_download_file')]
    public function downloadFile(string $fileName): Response
    {
        $filePath = sprintf('fichiertxt/%s', $fileName);

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier demandé n\'existe pas.');
        }
    
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);
    
        return $response;
    }
}
