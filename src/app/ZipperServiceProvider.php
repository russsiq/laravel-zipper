<?php

namespace Russsiq\Zipper;

// Базовые расширения PHP.
use ZipArchive;

// Сторонние зависимости.
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Russsiq\Zipper\Support\Zipper;

class ZipperServiceProvider extends ServiceProvider
{
    /**
     * Регистрация Класса-обертки для архиватора ZipArchive.
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('zipper', function (Application $app) {
            return new Zipper(
                $app->make('files'),
                new ZipArchive
            );
        });
    }
}
