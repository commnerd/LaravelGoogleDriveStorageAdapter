<?php

namespace GoogleDriveStorage;

use Google_Client as ProprietaryGoogleClient;
use Google_Service_Drive;
use Cache;

class GoogleClient extends ProprietaryGoogleClient
{
    const TOKEN_KEY = "storage_google_drive_refresh_token";

    private $config;

    public function __construct()
    {
        $this->config = config('filesystems.disks.google_drive');

        parent::__construct([
            'application_name' => config("app.name"),

            // https://developers.google.com/console
            'client_id' => $this->config["client_id"],
            'client_secret' => $this->config["client_secret"],

            // Other OAuth2 parameters.
            'include_granted_scopes' => [Google_Service_Drive::DRIVE],
            'api_format_v2' => true,
        ]);

        $this->setAccessToken($this->getToken());
    }

    private function getToken() {
        $token = Cache::get(self::TOKEN_KEY);
        if(isset($token)) {
            return $token;
        }
        $token = $this->fetchAccessTokenWithRefreshToken($this->config['refresh_token']);
        Cache::put(self::TOKEN_KEY, $token, $token["expires_in"]);
        return $token;
    }

}
