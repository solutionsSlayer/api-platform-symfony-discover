<?php

namespace App\Entity\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private  OpenApiFactoryInterface $decorated) { }

    public function __invoke(array $context = []): OpenApi
    {
        return $this->decorated->__invoke($context);
    }
}