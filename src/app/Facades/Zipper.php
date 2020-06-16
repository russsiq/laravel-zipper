<?php

namespace Russsiq\Zipper\Facades;

// Сторонние зависимости.
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Russsiq\Zipper\Contracts\ZipperContract open(string $filename);
 * @method static \Russsiq\Zipper\Contracts\ZipperContract create(string $filename);
 *
 * @see \Russsiq\Zipper\Contracts\ZipperContract
 * @see \Russsiq\Zipper\Support\Zipper
 */
class Zipper extends Facade
{
    /**
     * Получить зарегистрированное имя компонента.
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'zipper';
    }
}
