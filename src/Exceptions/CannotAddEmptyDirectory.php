<?php

namespace Russsiq\Zipper\Exceptions;

// Исключения.
use Russsiq\Zipper\Exceptions\ZipperException;

/**
 * [CannotAddEmptyDirectory description]
 */
class CannotAddEmptyDirectory extends ZipperException
{
    /**
     * Полный путь к файлу.
     * @var string
     */
    private $zipname;

    /**
     * Относительный путь путь добавляемой директории.
     * @var string
     */
    private $dirname;

    /**
     * Код ошибки.
     * @var int
     */
    private $status;

    /**
     * Текстовое представление кода ошибки.
     * @var string
     */
    private $reason;

    /**
     * Создать новый экземпляр Исключения.
     * @param  string  $zipname
     * @param  string  $dirname
     * @param  int  $status
     * @param  string|null  $reason
     * @return self
     */
    public static function make(string $zipname, string $dirname, int $status, string $reason = null): self
    {
        $reason = $reason ?: parent::getErrorFromStatus($status);

        $instance = new self(
            "Can't add empty directory [{$dirname}] to zip archive [{$zipname}]. Reason: {$reason}",
        );

        $instance->zipname = $zipname;
        $instance->dirname = $dirname;
        $instance->status = $status;
        $instance->reason = $reason;

        return $instance;
    }

    /**
     * Получить полный путь к файлу.
     * @return string
     */
    public function zipname(): string
    {
        return $this->zipname;
    }

    /**
     * Получить относительный путь путь добавляемой директории.
     * @return string
     */
    public function dirname(): string
    {
        return $this->dirname;
    }

    /**
     * Получить код ошибки.
     * @return int
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * Получить текстовое представление кода ошибки.
     * @return string
     */
    public function reason(): string
    {
        return $this->reason;
    }
}
