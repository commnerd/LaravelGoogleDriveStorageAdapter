<?php

namespace Tests;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Cache\ArrayStore;
use Storage;

class GoogleDriveStorageProviderTest extends TestCase
{
    public function setUp(): void {
        parent::setUp();
    }

    public function testDiskRegistration()
    {
        $this->assertTrue(Storage::disk("google_drive") instanceof Filesystem);
    }

    public function testDefaultDrive()
    {
        $this->assertTrue(Storage::disk("google_drive") === Storage::disk(config("filesystems.default")));
    }
}