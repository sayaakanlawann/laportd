<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Daftarkan pencegat login Filament
        $this->app->singleton(
            \Filament\Http\Responses\Auth\Contracts\LoginResponse::class,
            \App\Http\Responses\FilamentLoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Storage::extend('google', function($app, $config) {
            $client = new \Google\Client();
            
            // Menggunakan Kunci VVIP (OAuth)
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            $client->refreshToken($config['refreshToken']);
            
          $service = new \Google\Service\Drive($client);
            
            // KUNCI PERBAIKAN:
            // Kita bersihkan ID dari spasi/enter tersembunyi menggunakan trim()
            $folderId = trim($config['folderId']);
            
            // Kita kembalikan ID ke posisi seharusnya (Parameter Kedua)
            $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $folderId);

            return new \Illuminate\Filesystem\FilesystemAdapter(
                new \League\Flysystem\Filesystem($adapter),
                $adapter,
                $config
            );
        });
    }
}