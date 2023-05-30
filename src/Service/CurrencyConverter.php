<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyConverter
{
    private HttpClientInterface $client;
    private ParameterBagInterface $parameterBag;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $parameterBag)
    {
        $this->client = $httpClient;
        $this->parameterBag = $parameterBag;
    }

    public function convertEurToDollar(float $euroPrice): float
    {
        return $euroPrice * $this->getRate('EUR');
    }

    public function convertEurToYen(float $euroPrice): float
    {
        return $euroPrice * $this->getRate('JPY');
    }

    public function getRate(string $currency)
    {
        $currencyRate = 1;

        $apiKey = $this->parameterBag->get('exchange_api_key');

        $url = 'https://v6.exchangerate-api.com/v6/' . $apiKey . '/latest/USD';

        $response = $this->client->request(
            'GET',
            $url,
        );

        $statusCode = $response->getStatusCode();

        $contentType = $response->getHeaders()['content-type'][0];

        if ($statusCode === 200 && $contentType === 'application/json') {
            $content = $response->toArray();
            $currencyRate = $content['conversion_rates'][$currency];
        }

        return 1/$currencyRate;
    }
}
