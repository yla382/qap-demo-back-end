<?php

declare(strict_types=1);

namespace App\UserInterface;

use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;
use Symfony\Component\Routing\Attribute\Route;
use Monolog\Logger;
use App\Library\LoggerHandler;

/**
 * WelcomeController
 *
 * @package App\UserInterface
 */
final class VendorApiController
{
    private string $dataDir;
    private $logger;

    public function __construct()
    {
        // Directory where the JSON files are stored
        $this->dataDir = __DIR__ . '/../../data/';

        $this->logger = LoggerHandler::createLogger();
    }

    #[Route(path: '/vendors', name: 'vendors')]
    public function getVendors(): ResponseInterface
    {
        $vendorsFile = $this->dataDir . 'vendors.json';

        if (!file_exists($vendorsFile)) {
            $this->logger->error('No accounts.json exists to extract the data');
            
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
