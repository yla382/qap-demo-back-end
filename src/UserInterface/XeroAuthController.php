<?php

namespace App\UserInterface;

require_once __DIR__ .'/../../src/Library/XeroApiLibrary.php';

use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Library\XeroApiLibrary;

final class XeroAuthController
{
    private $provider;

    public function __construct()
    {
        session_start();
        
        $this->provider = new GenericProvider([
            'clientId'                => $_ENV['XERO_CLIENT_ID'], 
            'clientSecret'            => $_ENV['XERO_CLIENT_SECRET'],
            'redirectUri'             => $_ENV['XERO_REDIRECT_URI'], 
            'urlAuthorize'            => $_ENV['XERO_AUTH_URL'],
            'urlAccessToken'          => $_ENV['XERO_TOKEN_URL'],    
            'urlResourceOwnerDetails' => $_ENV['XERO_USER_URI'], 
        ]);
    }

    #[Route(path: '/xero/authenticate', name: 'xero_authenticate')]
    public function authenticate(): ResponseInterface
    {
        $options = [
            'scope' => ['openid email profile offline_access accounting.settings accounting.transactions accounting.contacts accounting.journals.read accounting.reports.read accounting.attachments']
        ];

        $authorizationUrl = $this->provider->getAuthorizationUrl($options);
        $_SESSION['oauth2state'] = $this->provider->getState();
        return new Response(302, ['Location' => $authorizationUrl]);
    }

    #[Route(path: '/xero/redirect', name: 'xero_redirect')]
    public function redirect(): ResponseInterface
    {
         // Validate the OAuth2 state to prevent CSRF
         if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            return new Response(400, [], 'Invalid state parameter.');
        }

        try {
             // Get the authorization code from the query string
            $authorizationCode = $_GET['code'];

            // Exchange the authorization code for an access token
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $authorizationCode
            ]);

            $_SESSION['xero_access_token'] = $accessToken->getToken();
            $_SESSION['xero_refresh_token'] = $accessToken->getRefreshToken();
            $_SESSION['xero_access_token_expires'] = time() + $accessToken->getExpires();

            // Fetch the tenant ID
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', 'https://api.xero.com/connections', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken->getToken(),
                    'Accept'        => 'application/json',
                ],
            ]);

            $connections = json_decode($response->getBody(), true);

            if (!empty($connections)) {
                // Save the access token in the session
                $_SESSION['xero_tenant_id'] = $connections[0]['tenantId'];
            }

            // Get accounts and store as JSON
            $xeroApi = new XeroApiLibrary();
            $xeroApi->getAccounts();
            $xeroApi->getVendors();


            // Redirect to front-end
            return new Response(302, ['Location' => 'http://localhost:3001?authStatus=success']);
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'message' => 'Error obtaining access token: ' . $e->getMessage(),
            ]));
        }
    }
}
