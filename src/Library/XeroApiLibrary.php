<?php

namespace App\Library;

use League\OAuth2\Client\Provider\GenericProvider;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Configuration;
use GuzzleHttp\Client;

class XeroApiLibrary
{
    private $provider;
    private $accountingApi;

    public function __construct()
    {
        // Initialize OAuth provider
        $this->provider = new GenericProvider([
            'clientId'                => $_ENV['XERO_CLIENT_ID'],
            'clientSecret'            => $_ENV['XERO_CLIENT_SECRET'], 
            'redirectUri'             => $_ENV['XERO_REDIRECT_URI'],
            'urlAuthorize'            => $_ENV['XERO_AUTH_URL'],     
            'urlAccessToken'          => $_ENV['XERO_TOKEN_URL'],  
            'urlResourceOwnerDetails' => $_ENV['XERO_USER_URI'], 
        ]);
        
        // Initialize the Accounting API instance
        $this->accountingApi = new AccountingApi(new Client(), $this->getConfig());
    }

    // Helper function to get the OAuth2 configuration
    private function getConfig(): Configuration
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new \Exception('Access token is not available');
        }

        return Configuration::getDefaultConfiguration()->setAccessToken($accessToken);
    }

    // Function to get the current access token
    private function getAccessToken(): ?string
    {
        if (!isset($_SESSION['xero_access_token']) || !isset($_SESSION['xero_refresh_token'])) {
            return null;
        }

        // Check if access token is expired and refresh it if needed
        $expiry = $_SESSION['xero_access_token_expires'] ?? 0;
        if (time() >= $expiry) {
            return $this->refreshAccessToken();
        }

        return $_SESSION['xero_access_token'];
    }

    // Function to refresh the access token using the refresh token
    private function refreshAccessToken(): ?string
    {
        try {
            $accessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $_SESSION['xero_refresh_token'],
            ]);

            // Save new access token and its expiration time
            $_SESSION['xero_access_token'] = $accessToken->getToken();
            $_SESSION['xero_access_token_expires'] = time() + $accessToken->getExpires();
            return $_SESSION['xero_access_token'];
        } catch (\Exception $e) {
            return null;
        }
    }

    // Function to fetch accounts from Xero API and return them as JSON
    public function getAccounts(): array
    {
        try {
            $tenantId = $_SESSION['xero_tenant_id'] ?? null;
            if (!$tenantId) {
                throw new \Exception('Tenant ID is not available');
            }

            $where = "Status==\"ACTIVE\"";
            $order = "Name ASC";
            // Fetch accounts using the Xero API
            $result = $this->accountingApi->getAccounts($tenantId);

            // Process and store the account data
            $accounts = $result->getAccounts();
            $this->storeDataToJsonFile('accounts.json', $accounts);
        } catch (\Exception $e) {
            throw new \RuntimeException('Error fetching accounts: ' . $e->getMessage());
        }

        return $accounts;
    }

    public function getVendors(): array
    {
        try{
            $tenantId = $_SESSION['xero_tenant_id'] ?? null;
            if (!$tenantId) {
                throw new \Exception('Tenant ID is not available');
            }

            // Filter to get only suppliers
            $where = "IsSupplier==true";
            $order = "Name ASC";
            $summaryOnly = false; // Enable summary mode to reduce the data payload
            $page = 1;  // Get the first page of results
            $pageSize = 100;  // Number of results per page
            $vendors = [];

            do {
                // Fetch suppliers using Xero's Accounting API (paginated results)
                $result = $this->accountingApi->getContacts(
                    $tenantId,
                    null,
                    $where,
                    $order,
                    null,
                    $page,
                    null, // Include archived (set to false)
                    $summaryOnly,
                    null,  // Search term (optional)
                    $pageSize
                );
                // Add suppliers to the list
                $vendors = array_merge($vendors, $result->getContacts());

                // Pagination check: If the page count is greater than the current page, increment the page
                $pagination = $result->getPagination();
                if ($pagination->getPage() < $pagination->getPageCount()) {
                    $page++;
                } else {
                    break;
                }
            } while ($page <= $pagination->getPageCount());
            
            $this->storeDataToJsonFile('vendors.json', $vendors);
        } catch (\Exception $e) {
            throw new \RuntimeException('Error fetching vendors: ' . $e->getMessage());
        }

        return $vendors;
    }

    // Function to store data in JSON file
    private function storeDataToJsonFile(string $filename, array $data): void
    {
        // Ensure the 'data' directory exists
        $directory = __DIR__ . '/../../data';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $dataStrings = array_map(function ($array_data) {
            return json_decode((string)$array_data, true); // Convert Contact object to string using __toString()
        }, $data);

        // Store the data in a JSON file
        $filePath = $directory . '/' . $filename;
        file_put_contents($filePath, json_encode($dataStrings, JSON_PRETTY_PRINT));
    }
}