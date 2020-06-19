<?php

namespace Russsiq\Zipper\Exceptions;

// Исключения.
use Russsiq\Zipper\Exceptions\ZipperException;

/**
 * [CannotDeleteElement description]
 */
class CannotDeleteElement extends ZipperException
{
    /**
     * Полный путь к файлу.
     * @var string
     */
    private $zipname;

    /**
     * Имя удаляемого элемента (файла или каталога).
     * @var string
     */
    private $element;

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
     * @param  string  $element
     * @param  int  $status
     * @param  string|null  $reason
     * @return self
     */
    public static function make(string $zipname, string $element, int $status, string $reason = null): self
    {
        $reason = $reason ?: parent::getErrorFromStatus($status);

        $instance = new self(
            "Can't delete element [{$element}] from zip archive [{$zipname}]. Reason: {$reason}",
        );

        $instance->zipname = $zipname;
        $instance->element = $element;
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
     * Получить имя удаляемого элемента (файла или каталога).
     * @return string
     */
    public function element(): string
    {
        return $this->element;
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
