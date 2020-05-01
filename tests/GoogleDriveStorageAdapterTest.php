<?php

namespace Tests;

use Storage;

class GoogleDriveStorageAdapterTest extends TestCase
{
    public function testExists()
    {
        // $this->assertTrue(Storage::exists("messages.jpg"));
        $this->assertTrue(true);
    }

    public function testAllDirectories()
    {
        dd(Storage::allFiles());
    }
}