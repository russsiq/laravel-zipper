<?php

namespace Russsiq\Zipper\Exceptions;

// Исключения.
use Russsiq\Zipper\Exceptions\ZipperException;

/**
 * [CannotAddFile description]
 */
class CannotAddFile extends ZipperException
{
    /**
     * Полный путь к файлу.
     * @var string
     */
    private $zipname;

    /**
     * Абсолютный путь добавляемого файла.
     * @var string
     */
    private $filename;

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
    public static function make(string $zipname, string $filename, int $status, string $reason = null): self
    {
        $reason = $reason ?: parent::getErrorFromStatus($status);

        $instance = new self(
            "Can't add file [{$filename}] to zip archive [{$zipname}]. Reason:{$reason}"
        );

        $instance->zipname = $zipname;
        $instance->filename = $filename;
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
     * Получить абсолютный путь добавляемого файла.
     * @return string
     */
    public function filename(): string
    {
        return $this->filename;
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
