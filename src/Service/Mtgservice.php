<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Mtgservice
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getCardImage(string $cardName): ?string
    {
        // formater le nom pour le forma url
        $cardName = urlencode($cardName);

        // requête GET vers l'API Scryfall
        $response = $this->httpClient->request('GET', 'https://api.scryfall.com/cards/named?exact=' . $cardName);

        // si la carte est pas trouvée
        if ($response->getStatusCode() !== 200) {
            return null;
        }

        // Stoquer les donner envoyer par scyfall
        $cardData = $response->toArray();

        // récupérer l'image de la carte
        if (isset($cardData['image_uris']['normal'])) {
            return $cardData['image_uris']['normal'];
        }

        return null;
    }
}
