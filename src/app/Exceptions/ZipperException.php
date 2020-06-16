<?php

namespace Russsiq\Zipper\Exceptions;

// Исключения.
use RuntimeException;
use Throwable;

// Базовые расширения PHP.
use ZipArchive;

/**
 * Исключения Класса-обертки.
 */
class ZipperException extends RuntimeException
{
    /**
     * Создать новый экземпляр Исключения.
     * @param  string  $message
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    protected function __construct(string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function getErrorFromStatus(int $status)
    {
        $errors = [
            ZipArchive::ER_OK => 'No error.',
            ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported.',
            ZipArchive::ER_RENAME => 'Renaming temporary file failed.',
            ZipArchive::ER_CLOSE => 'Closing zip archive failed.',
            ZipArchive::ER_SEEK => 'Seek error.',
            ZipArchive::ER_READ => 'Read error.',
            ZipArchive::ER_WRITE => 'Write error.',
            ZipArchive::ER_CRC => 'CRC error.',
            ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed.',
            ZipArchive::ER_NOENT => 'No such file.',
            ZipArchive::ER_EXISTS => 'File already exists.',
            ZipArchive::ER_OPEN => 'Can\'t open file.',
            ZipArchive::ER_TMPOPEN => 'Failure to create temporary file.',
            ZipArchive::ER_ZLIB => 'Zlib error.',
            ZipArchive::ER_MEMORY => 'Memory allocation failure.',
            ZipArchive::ER_CHANGED => 'Entry has been changed.',
            ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported.',
            ZipArchive::ER_EOF => 'Premature EOF.',
            ZipArchive::ER_INVAL => 'Invalid argument.',
            ZipArchive::ER_NOZIP => 'Not a zip archive.',
            ZipArchive::ER_INTERNAL => 'Internal error.',
            ZipArchive::ER_INCONS => 'Zip archive inconsistent.',
            ZipArchive::ER_REMOVE => 'Can\'t remove file.',
            ZipArchive::ER_DELETED => 'Entry has been deleted.',

        ];

        return $errors[$status] ?? "Unknown status: $status";
    }
}
