<?php

namespace GoogleDriveStorage;

use Google_Service_Drive;
use Google_Client as ProprietaryGoogleClient;

class GoogleClient extends ProprietaryGoogleClient
{
    public $accessToken;

    public function __construct()
    {
        parent::__construct([
            'application_name' => '',

            // https://developers.google.com/console
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => null,
            'state' => null,

            // Simple API access key, also from the API console. Ensure you get
            // a Server key, and not a Browser key.
            'developer_key' => '',

            // Other OAuth2 parameters.
            'include_granted_scopes' => [Google_Service_Drive::DRIVE],

            // Task Runner retry configuration
            // @see Google_Task_Runner
            'retry' => array(),
            'retry_map' => null,

            // cache config for downstream auth caching
            'cache_config' => [],

            // function to be called when an access token is fetched
            // follows the signature function ($cacheKey, $accessToken)
            'token_callback' => null,

            // Service class used in Google_Client::verifyIdToken.
            // Explicitly pass this in to avoid setting JWT::$leeway
            'jwt' => null,

            // Setting api_format_v2 will return more detailed error messages
            // from certain APIs.
            'api_format_v2' => false
        ]);

        $this->accessToken = $this->fetchAccessTokenWithRefreshToken(config('refreshToken'));
    }
}