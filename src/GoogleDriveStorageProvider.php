<?php

namespace GoogleDriveStorage;

use Illuminate\Support\ServiceProvider;
use Illuminate\Config\Repository as Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

class GoogleDriveStorageProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Config $config)
    {
        $this->publishes([
            __DIR__.'/../config/storage_google_drive.php' => config_path('storage_google_drive.php'),
        ], 'config');

        $config["filesystems.disks.google_drive"] = include __DIR__."/../configs/storage_google_drive.php";

        if($config["filesystems.disks.google_drive.force_default"]) {
            $config["filesystems.default"] = "google_drive";
            if(is_link(public_path("storage"))) {
                unlink(public_path("storage"));
            }
        }

        $client = new GoogleClient;

        $service = new GoogleDriveService($client);

        $storageAdapter = new GoogleDriveStorageAdapter($service, $config["filesystems.disks.google_drive"]);

        Storage::extend('google_drive', function () use ($storageAdapter) {
            return $storageAdapter;
        });

        Route::get('/storage/{path}', function(string $path) {
            $data = Storage::get($path);
            $finfo = new \finfo(FILEINFO_MIME);
            return response($data)
                ->header("Content-Type", $finfo->buffer($data));
        })->where('path', '.*');
    }
}
