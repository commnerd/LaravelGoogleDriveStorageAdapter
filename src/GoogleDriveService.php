<?php

namespace GoogleDriveStorage;

use Google_Service_Drive;

class GoogleDriveService extends Google_Service_Drive
{
    private $client;

    public function __construct(GoogleClient $client)
    {
        $this->client = $client;
    }
}
