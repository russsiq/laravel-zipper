<?php

namespace Russsiq\Zipper\Exceptions;

// Исключения.
use Russsiq\Zipper\Exceptions\ZipperException;

/**
 * [UnableToExtractArchive description]
 */
class UnableToExtractArchive extends ZipperException
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
     * @param  string|null  $reason
     * @return self
     */
    public static function make(string $zipname, int $status, string $reason = null): self
    {
        $reason = $reason ?: parent::getErrorFromStatus($status);

        $instance = new self(
            "Unable to extract zip archive [{$zipname}]. Reason: {$reason}"
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
