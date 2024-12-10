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
final class AccountApiController
{
    private string $dataDir;
    private $logger;

    public function __construct()
    {
        // Directory where the JSON files are stored
        $this->dataDir = __DIR__ . '/../../data/';

        $this->logger = LoggerHandler::createLogger();
    }

    #[Route(path: '/accounts', name: 'accounts')]
    public function getAccounts(): ResponseInterface
    {
        $accountsFile = $this->dataDir . 'accounts.json';

        if (!file_exists($accountsFile)) {
            $this->logger->error('No accounts.json exists to extract the data');
            
            return new Response(404, 
                [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
                ], 
                json_encode(['error' => 'Accounts data not found'])
            );
        }

        // Read the accounts data from the JSON file
        $accountsData = file_get_contents($accountsFile);

        // Return the accounts data as a JSON response
        return new Response(200, 
            [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            ], 
            $accountsData
        );
    }
}
