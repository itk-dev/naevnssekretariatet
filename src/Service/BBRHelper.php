<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BBRHelper
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get url to BBR-Meddelelse for an address.
     *
     * @param string $address the address
     * @param string $format  The format. Current only 'pdf' is supported.
     *
     * @return string The url to the BBR-Meddelelse
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \InvalidArgumentException
     */
    public function getBBRMeddelelseUrl(string $address, string $format = 'pdf'): string
    {
        if ('pdf' !== $format) {
            throw new \InvalidArgumentException(sprintf('Invalid format: %s', $format));
        }

        // @see https://dawadocs.dataforsyningen.dk/dok/bbr#find-en-bygning-ud-fra-dens-adgangsadresse
        $response = $this->httpClient->request('GET', 'https://api.dataforsyningen.dk/adgangsadresser', [
            'query' => ['q' => $address],
        ]);
        $data = $response->toArray();
        if (1 !== count($data) || !isset($data[0]['id'])) {
            throw new \InvalidArgumentException(sprintf('Invalid, unknown or ambiguous address: %s', $address));
        }
        $accessAddressId = $data[0]['id'];

        $response = $this->httpClient->request('GET', 'https://api.dataforsyningen.dk/bbrlight/opgange', [
            'query' => ['adgangsadresseid' => $accessAddressId],
        ]);
        $data = $response->toArray();
        if (1 !== count($data) || !isset($data[0]['Bygning_id'])) {
            throw new \InvalidArgumentException(sprintf('Cannot get building id for address %s', $address));
        }
        $buildingId = $data[0]['Bygning_id'];

        $response = $this->httpClient->request('GET', 'https://api.dataforsyningen.dk/bbrlight/bygninger', [
            'query' => ['id' => $buildingId],
        ]);
        $data = $response->toArray();
        if (1 !== count($data) || !isset($data[0]['KomKode'], $data[0]['ESREjdNr'])) {
            throw new \InvalidArgumentException(sprintf('Cannot get building id for address %s', $address));
        }
        ['KomKode' => $municipalityCode, 'ESREjdNr' => $propertyIdentifier] = $data[0];

        return 'https://bbr.dk/pls/wwwdata/get_ois_pck.show_bbr_meddelelse_pdf?'.http_build_query([
                'i_municipalitycode' => $municipalityCode,
                'i_realpropertyidentifier' => $propertyIdentifier,
            ]);
    }
}
