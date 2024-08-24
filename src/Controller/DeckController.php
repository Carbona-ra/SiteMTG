<?php
namespace App\Controller;

use App\Entity\Deck;
use App\Form\DeckType;
use App\Repository\DeckRepository;
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
    #[Route('/', name: 'app_deck_index', methods: ['GET'])]
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

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

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
                    // Handle the exception if something happens during file upload
                }

                $deck->setImageName($newFilename);
            }

            $entityManager->persist($deck);
            $entityManager->flush();

            return $this->redirectToRoute('app_deck_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('deck/new.html.twig', [
            'deck' => $deck,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_deck_show', methods: ['GET'])]
    public function show(Deck $deck): Response
    {

        $user = $this->getUser();
        $isOwner = ($deck->getCreator() === $user); // savoir si c'est le proprio du deck
        
        return $this->render('deck/show.html.twig', [
            'deck' => $deck,
            'cards' => $deck->getAddTo(),  //ici on chope la liste de carte du deck
            'isOwner' => $isOwner,
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
        // Get the image file from the form
        $imageFile = $form->get('imageFile')->getData();

        if ($imageFile) {
            // Remove the old image if it exists
            if ($deck->getImageName()) {
                $oldFilename = $deck->getImageName();
                $oldFilepath = $this->getParameter('deck_images_directory').'/'.$oldFilename;
                
                if (file_exists($oldFilepath)) {
                    unlink($oldFilepath);
                }
            }

            // Process the new image file
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('deck_images_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // Handle the exception if something happens during file upload
                $this->addFlash('error', 'Failed to upload the image.');
                return $this->render('deck/edit.html.twig', [
                    'deck' => $deck,
                    'form' => $form,
                ]);
            }

            $deck->setImageName($newFilename);
        }

        // Update the deck entity
        $entityManager->flush();

        return $this->redirectToRoute('app_deck_index', [], Response::HTTP_SEE_OTHER);
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

        if ($deck->getCreator() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$deck->getId(), $request->get('_token'))) {
            $entityManager->remove($deck);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_deck_index', [], Response::HTTP_SEE_OTHER);
    }
}
