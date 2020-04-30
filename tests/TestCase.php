<?php

namespace Tests;

use GoogleDriveStorage\GoogleDriveStorageProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * setUp the test harness
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [GoogleDriveStorageProvider::class];
    }
}