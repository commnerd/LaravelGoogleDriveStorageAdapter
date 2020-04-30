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

        $this->app->singleton(GoogleClient::class, function ($app) {
            return new GoogleClient();
        });

        $this->app->singleton(GoogleDriveService::class, function ($app) {
            return new GoogleDriveService($app->make(GoogleClient::class));
        });

        Storage::extend('google_drive', function () {
            return new GoogleDriveStorageAdapter(app()->make(GoogleDriveService::class));
        });

        app()->config["filesystems.disks.google_drive"] = [
            'driver' => 'google_drive',
            'refreshToken' => "1234567890",
        ];
    }
}
