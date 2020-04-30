<?php

namespace Tests;

use Storage;

class GoogleDriveStorageProviderTest extends TestCase
{
    public function testDiskRegistration()
    {
        dd(Storage::disk("google_drive"));
    }
}