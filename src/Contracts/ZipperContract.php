<?php

namespace Russsiq\Zipper\Contracts;

// Базовые расширения PHP.
use ZipArchive;

/**
 * Контракт публичных методов ZIP Архиватора.
 * @var interface
 */
interface ZipperContract
{
    /**
     * Получить полный путь, включая имя, текущего рабочего архива.
     * @return string|null
     */
    public function filename(): ?string;

    /**
     * Открыть архив для последующей работы с ним (для чтения, записи или изменения).
     * @param  string  $filename
     * @return self
     */
    public function open(string $filename): self;

    /**
     * Создать архив для последующей работы с ним
     * (для чтения, записи или изменения).
     * @param  string  $filename
     * @return self
     */
    public function create(string $filename): self;

    /**
     * Извлечь весь архив или его части в указанное место назначения.
     * @param  string  $destination  Место назначение, куда извлекать файлы.
     * @param  array|null  $entries  Массив элементов для извлечения.
     * @return bool
     */
    public function extractTo(string $destination, array $entries = null): bool;

    /**
     * Добавить в архив файл, используя содержимое строки.
     * @param  string  $localname  Относительный путь к файлу в архиве, включая его имя.
     * @param  string  $contents  Содержимое для создания файла. Используется в двоичном безопасном режиме.
     * @return bool
     */
    public function addFromString(string $localname, string $contents) : bool;

    /**
     * Добавить в архив файл по указанному пути.
     * @param  string  $filename
     * @param  string|null  $localname
     * @return bool
     */
    public function addFile(string $filename, string $localname = null): bool;

    /**
     * Добавить в архив директорию.
     * @param  string  $realPath
     * @param  string  $relativePath
     * @return bool
     */
    public function addDirectory(string $realPath, string $relativePath): bool;

    /**
     * Добавить в архив пустую директорию.
     * @param  string  $dirname
     * @return bool
     */
    public function addEmptyDirectory(string $dirname): bool;

    /**
     * Удалить элемент (файл) из архива, используя его имя.
     * @param  string  $filename
     * @return bool
     */
    public function deleteFile(string $filename): bool;

    /**
     * Удалить элемент (каталог) из архива, используя его имя.
     * @param  string  $dirname
     * @return bool
     */
    public function deleteDirectory(string $dirname): bool;

    /**
     * Закрыть текущий (открытый или созданный) архив и сохранить изменения.
     * @return bool
     */
    public function close(): bool;
}
