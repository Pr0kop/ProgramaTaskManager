<?php

declare(strict_types=1);

namespace App\Infrastructure\User\External;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class JsonPlaceholderClient
{
    private const BASE_URL = 'https://jsonplaceholder.typicode.com';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {}

    /** @return array<int, array> */
    public function fetchUsers(): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL . '/users');

        return $response->toArray();
    }
}
