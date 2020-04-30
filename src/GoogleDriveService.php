<?php

namespace GoogleDriveStorage;

use Google_Service_Drive;

class GoogleDriveService extends Google_Service_Drive
{
    private $client;

    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setClientId($this->ClientId);
        $this->client->setClientSecret($this->ClientSecret);
        $this->client->refreshToken($this->refreshToken);
    }
}