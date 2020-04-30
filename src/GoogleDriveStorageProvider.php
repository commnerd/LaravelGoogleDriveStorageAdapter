<?php

namespace GoogleDriveStorage;

use Illuminate\Support\ServiceProvider;
use Storage;

class GoogleDriveStorageProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->publishes([
        //     __DIR__.'/../config/storage_google_drive.php' => config_path('storage_google_drive.php'),
        // ], 'config');

        Storage::extend('google_drive', function ($app, $config) {
            return new GoogleDriveStorageAdapter();
        });

        app()->config["filesystems.disks.google_drive"] = [
            'driver' => 'google_drive'
        ];
    }
}
