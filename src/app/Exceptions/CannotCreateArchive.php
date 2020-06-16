<?php

namespace Russsiq\Zipper\Exceptions;

// Исключения.
use Russsiq\Zipper\Exceptions\ZipperException;

/**
 * [CannotCreateArchive description]
 */
class CannotCreateArchive extends ZipperException
{
    /**
     * Полный путь к файлу.
     * @var string
     */
    private $zipname;

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
     * @param  int  $status
     * @return self
     */
    public static function make(string $zipname, int $status): self
    {
        $reason = parent::getErrorFromStatus($status);

        $instance = new self(
            "Can't create zip archive [{$zipname}]. Reason: {$reason}"
        );

        $instance->zipname = $zipname;
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
