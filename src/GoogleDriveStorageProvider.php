<?php

namespace GoogleDriveStorage;

use Illuminate\Support\ServiceProvider;
use Illuminate\Config\Repository as Config;
use Storage;

class GoogleDriveStorageProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Config $config)
    {
        // $this->publishes([
        //     __DIR__.'/../config/storage_google_drive.php' => config_path('storage_google_drive.php'),
        // ], 'config');

        $config["filesystems.disks.google_drive"] = [
            'driver' => 'google_drive',
            'refresh_token' => "blah",
            "client_id" => "blah",
            "client_secret" => "blah",
            "root" => "blah",
        ];

        $config["filesystems.default"] = "google_drive";

        $client = new GoogleClient;

        $service = new GoogleDriveService($client);

        $storageAdapter = new GoogleDriveStorageAdapter($service, $config);

        return Storage::extend('google_drive', function () use ($storageAdapter) {
            return $storageAdapter;
        });
    }
}
