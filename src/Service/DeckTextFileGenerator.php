<?php
namespace App\Service;

use App\Entity\Deck;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeckTextFileGenerator
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function generate(Deck $deck): string
    {
        $lines = [];

        foreach ($deck->getAddTo() as $card) {
            $cardData = $this->fetchCardData($card->getName());

            if ($cardData) {
                $line = sprintf(
                    "1x %s (%s) %s [%s]",
                    $cardData['name'],
                    $cardData['set'],
                    $cardData['collector_number'],
                    $cardData['type_line']
                );
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }

    public function fetchCardData(string $cardName): ?array
    {
        
        $response = $this->httpClient->request(
            Request::METHOD_GET, 
            'https://api.scryfall.com/cards/named', 
            [
                'query' => ['exact' => $cardName],
            ]
        );

        if ($response->getStatusCode() === 200) {
            return $response->toArray();
        }

        return null;
    }

    public function saveToFile(Deck $deck, string $filePath): void
    {
        $dir = dirname($filePath);

        // verif si le répertoire existe
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Le répertoire "%s" n\'a pas pu être créé.', $dir));
            }
        }

        // générer le contenu du fichier 
        $content = $this->generate($deck);

        // save le contenu dans le fichier
        if (file_put_contents($filePath, $content) === false) {
            throw new \RuntimeException(sprintf('Impossible d\'écrire dans le fichier "%s".', $filePath));
        }
    }
}
