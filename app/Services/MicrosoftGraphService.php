<?php

namespace App\Services;

use GuzzleHttp\Client;

class MicrosoftGraphService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getAccessToken(): mixed
    {
        $response = $this->client->post('https://login.microsoftonline.com/' . env('MICROSOFT_TENANT_ID') . '/oauth2/v2.0/token', [
            'form_params' => [
                'client_id' => env('MICROSOFT_CLIENT_ID'),
                'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['access_token'];
    }

    public function createUser($userDetails)
    {
        $token = $this->getAccessToken();

        $jsonData = [
            'accountEnabled' => true,
            'displayName' => $userDetails['name']  . " " . $userDetails['usercode'],
            'mailNickname' => $userDetails['usercode'],
            'userPrincipalName' => $userDetails['usercode'] . '@anasacademy.uk',
            'passwordProfile' => [
                'forceChangePasswordNextSignIn' => true,
                'password' => $userDetails['password'],
            ],
        ];

        if(!empty($userDetails['first_name'])){
             $jsonData['givenName'] = $userDetails['first_name'];
        }

        if(!empty($userDetails['last_name'])){
             $jsonData['surname'] = $userDetails['last_name'];
        }

        $response = $this->client->post('https://graph.microsoft.com/v1.0/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => $jsonData,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function userExists($email)
    {
        $token = $this->getAccessToken();

        // Make a GET request to check if the user exists
        $response = $this->client->get('https://graph.microsoft.com/v1.0/users?$filter=userPrincipalName eq ' ."'$email'" , [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        // Check if the user exists
        return !empty($data['value']);
    }

    public function getAvailableLicenses()
    {
        $token = $this->getAccessToken();

        $response = $this->client->get('https://graph.microsoft.com/v1.0/subscribedSkus', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function assignLicense($userId, $skuId)
    {
        $token = $this->getAccessToken();

        $response = $this->client->post("https://graph.microsoft.com/v1.0/users/$userId/assignLicense", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'addLicenses' => [
                    ['skuId' => $skuId]
                ],
                'removeLicenses' => []
            ],
        ]);

        return json_decode($response->getBody(), true);
    }


}
