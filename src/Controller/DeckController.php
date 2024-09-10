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

#[Route('/deck')]
class DeckController extends AbstractController
{

    private $mtgService;

    public function __construct(Mtgservice $mtgService)
    {
        $this->mtgService = $mtgService;
    }

    #[Route('/search/deck', name: 'app_deck_search')]
    public function search(Request $request, DeckRepository $deckRepository): Response
    {
        $form = $this->createForm(DeckSearchType::class);
        $form->handleRequest($request);

        $decks = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $critaire = $form->getData();
            $decks = $deckRepository->findByCritaire($critaire);
        }

        return $this->render('deck/search.html.twig', [
            'form' => $form->createView(),
            'decks' => $decks,
        ]);
    }



    #[Route('/new', name: 'app_deck_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        $deck = new Deck();
        $deck->setCreator($user);
        $form = $this->createForm(DeckType::class, $deck);
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $imageFile = $form->get('imageFile')->getData();
    
                //gestion de l'image si il y en a une
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
    
                    try {
                        $imageFile->move(
                            $this->getParameter('deck_images_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'There was a problem uploading the file.');
                    }
    
                    $deck->setImageName($newFilename);
                }
    
                $entityManager->persist($deck);
                $entityManager->flush();

                $this->addFlash('felicitaschtroumpf', 'felicitaschtroumpf !');
    
                return $this->redirectToRoute('homepage', [], Response::HTTP_SEE_OTHER);
            } else {
                // Collecter les erreurs de validation
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                // Ajouter un message flash avec les erreurs
                $this->addFlash('error', implode(', ', $errors));
            }
        }
    
        return $this->render('deck/new.html.twig', [
            'deck' => $deck,
            'form' => $form->createView(),
        ]);
    }

    

    #[Route('/{id}', name: 'app_deck_show', methods: ['GET'])]
    public function show(Deck $deck): Response
    {
        // savoir si c'est le proprio du deck
        $user = $this->getUser();
        $isOwner = ($deck->getCreator() === $user); 

        // Récupérer le nom du commander du deck
        $commanderName = $deck->getCommanderName();

        // Utiliser le service MtgService pour récupérer l'image du commander
        $commanderImage = $this->mtgService->getCardImage($commanderName);

        // Récupérer les images des cartes du deck
        $cards = $deck->getAddTo(); 
        $cardImages = [];
        foreach ($cards as $card) {
            $cardName = $card->getName();
            $cardImages[$cardName] = $this->mtgService->getCardImage($cardName);
        }

        return $this->render('deck/show.html.twig', [
            'deck' => $deck,
            'cards' => $deck->getAddTo(), 
            'isOwner' => $isOwner,
            'cardImages' => $cardImages,
            'commanderImage' => $commanderImage, 
        ]);
    }

    #[Route('/{id}/edit', name: 'app_deck_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Deck $deck, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $user = $this->getUser();

    if ($deck->getCreator() !== $user) {
        throw $this->createAccessDeniedException();
    }

    $form = $this->createForm(DeckType::class, $deck);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        
        $imageFile = $form->get('imageFile')->getData();
        //gestion de limage si y'en a une
        if ($imageFile) {
            // on dégage lancienne siy'en a une
            if ($deck->getImageName()) {
                $oldFilename = $deck->getImageName();
                $oldFilepath = $this->getParameter('deck_images_directory').'/'.$oldFilename;
                
                if (file_exists($oldFilepath)) {
                    unlink($oldFilepath);
                }
            }
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('deck_images_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // si y'a un ptit flop pendant le telechargement
                $this->addFlash('error', 'Failed to upload the image.');
                return $this->render('deck/edit.html.twig', [
                    'deck' => $deck,
                    'form' => $form,
                ]);
            }
            $deck->setImageName($newFilename);
        }

        
        $entityManager->flush();

        return $this->redirectToRoute('homepage', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('deck/edit.html.twig', [
        'deck' => $deck,
        'form' => $form,
    ]);
}
    #[Route('/{id}', name: 'app_deck_delete', methods: ['POST'])]
    public function delete(Request $request, Deck $deck, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // vérif si c'est le proprio
        if ($deck->getCreator() !== $user) {
            throw $this->createAccessDeniedException();
        }
        
        if ($this->isCsrfTokenValid('delete'.$deck->getId(), $request->get('_token'))) {
            $entityManager->remove($deck);
            $entityManager->flush();
        }

        return $this->redirectToRoute('homepage', [], Response::HTTP_SEE_OTHER);
    }
}
