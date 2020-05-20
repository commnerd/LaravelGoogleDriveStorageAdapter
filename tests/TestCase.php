<?php

namespace Tests;

use GoogleDriveStorage\GoogleDriveStorageProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use GoogleDriveStorage\GoogleClient;

abstract class TestCase extends BaseTestCase
{

    protected $config;

    /**
     * setUp the test harness
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->config = [
            "force_default" => true,
            "driver" => "google_drive",
            "refresh_token" => "abcdefg",
            "client_id" => "hijklmnop",
            "client_secret" => "qrs",
            "root" => "tuv",
        ];

        app()->config["filesystems.disks.google_drive"] = $this->config;
    }
}