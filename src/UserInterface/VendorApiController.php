<?php

declare(strict_types=1);

namespace App\UserInterface;

use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * WelcomeController
 *
 * @package App\UserInterface
 */
final class VendorApiController
{
    private string $dataDir;

    public function __construct()
    {
        // Directory where the JSON files are stored
        $this->dataDir = __DIR__ . '/../../data/';
    }

    #[Route(path: '/vendors', name: 'vendors')]
    public function getVendors(): ResponseInterface
    {
        $vendorsFile = $this->dataDir . 'vendors.json';

        if (!file_exists($vendorsFile)) {
            return new Response(404, 
                [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
                ], 
                json_encode(['error' => 'Vendors data not found'])
            );
        }

        $vendorsData = file_get_contents($vendorsFile);

        // Return the vendors data as a JSON response
        return new Response(200, 
            [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            ], 
            $vendorsData
        );
    }
}
