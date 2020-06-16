<?php

namespace Russsiq\Zipper\Abstracts;

// Исключения.
use Russsiq\Zipper\Exceptions\ZipperException;
use Russsiq\Zipper\Exceptions\CannotAddEmptyDirectory;
use Russsiq\Zipper\Exceptions\CannotAddFile;
use Russsiq\Zipper\Exceptions\CannotCloseArchive;
use Russsiq\Zipper\Exceptions\CannotCreateArchive;
use Russsiq\Zipper\Exceptions\CannotDeleteElement;
use Russsiq\Zipper\Exceptions\CannotOpenArchive;
use Russsiq\Zipper\Exceptions\UnableToExtractArchive;
// use RuntimeException;

// Базовые расширения PHP.
use Countable;
use SplFileInfo;
use ZipArchive;

// Сторонние зависимости.
use Illuminate\Filesystem\Filesystem;
use Russsiq\Zipper\Contracts\ZipperContract;

/**
 * Абстрактный класс-обертка для архиватора.
 */
abstract class AbstractZipper implements ZipperContract, Countable
{
    /**
     * Экземпляр класса по работе с файловой системой.
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Экземпляр класса по работе с архивами.
     * @var ZipArchive
     */
    protected $ziparchive;

    /**
     * Создать новый экземпляр класса.
     * @param  Filesystem  $filesystem
     * @param  ZipArchive  $ziparchive
     */
    public function __construct(
        Filesystem $filesystem,
        ZipArchive $ziparchive
    ) {
        $this->filesystem = $filesystem;
        $this->ziparchive = $ziparchive;
    }

    // /**
    //  * Освобождение всех ссылок на текущий экземпляр класса.
    //  */
    // public function __destruct()
    // {
    //     // $this->close();
    // }

    /**
     * Получить количество файлов в архиве.
     * @return int
     */
    abstract public function count(): int;

    /**
     * Получить полный путь, включая имя, текущего рабочего архива.
     * @return string|null
     */
    abstract public function filename(): ?string;

    /**
     * Открыть архив для последующей работы с ним
     * (для чтения, записи или изменения).
     * @param  string  $filename
     * @return self
     */
    abstract public function open(string $filename): ZipperContract;

    /**
     * Создать архив для последующей работы с ним
     * (для чтения, записи или изменения).
     * @param  string  $filename
     * @return self
     */
    abstract public function create(string $filename): ZipperContract;

    /**
     * Извлечь весь архив или его части в указанное место назначения.
     * @param  string  $destination  Место назначение, куда извлекать файлы.
     * @param  array|null  $entries  Массив элементов для извлечения.
     * @return bool
     */
    abstract public function extractTo(string $destination, array $entries = null): bool;

    /**
     * Добавить в архив файл, используя содержимое строки.
     * @param  string  $localname  Относительный путь к файлу в архиве, включая его имя.
     * @param  string  $contents  Содержимое для создания файла. Используется в двоичном безопасном режиме.
     * @return bool
     */
    abstract public function addFromString(string $localname, string $contents) : bool;

    /**
     * Добавить в архив файл по указанному пути.
     * @param  string  $filename
     * @param  string|null  $localname
     * @return bool
     */
    abstract public function addFile(string $filename, string $localname = null) : bool;

    /**
     * Добавить в архив директорию.
     * @param  string  $realPath
     * @param  string  $relativePath
     * @return bool
     */
    abstract public function addDirectory(string $realPath, string $relativePath): bool;

    /**
     * Добавить в архив пустую директорию.
     * @param  string  $dirname
     * @return bool
     */
    abstract public function addEmptyDirectory(string $dirname): bool;

    /**
     * Удалить элемент (файл) из архива, используя его имя.
     * @param  string  $filename
     * @return bool
     */
    abstract public function deleteFile(string $filename): bool;

    /**
     * Удалить элемент (каталог) из архива, используя его имя.
     * @param  string  $dirname
     * @return bool
     */
    abstract public function deleteDirectory(string $dirname): bool;

    /**
     * Закрыть текущий (открытый или созданный) архив и сохранить изменения.
     * @return bool
     */
    abstract public function close(): bool;

    /**
     * Убедиться, что извлеченные файлы не имеют
     * посторонней вложенной директории,
     * т.е. исходники расположены в корневой директории.
     *
     * @param  string  $destination
     * @return void
     */
    public function ensureSourceInRootDirectory(string $destination)
    {
        $directories = $this->filesystem->directories($destination);

        if (1 === count($directories)) {
            $root = $directories[0];

            collect($this->filesystem->directories($root))
                ->each(function (string $directory) use ($destination) {
                    $this->filesystem->moveDirectory(
                        $directory,
                        $destination.DIRECTORY_SEPARATOR.$this->filesystem->name($directory)
                    );
                });

            collect($this->filesystem->files($root, true))
                ->each(function (SplFileInfo $file) use ($destination) {
                    $this->filesystem->move(
                        $file->getRealPath(),
                        $destination.DIRECTORY_SEPARATOR.$file->getFilename()
                    );
                });

            $this->filesystem->deleteDirectory($root);
        }
    }

    /**
     * Определить, произошла ли ошибка во время открытия архива.
     * @param  string  $zipname
     * @param  mixed  $status
     * @return void
     *
     * @throws CannotOpenArchive
     */
    protected function assertArchiveIsOpened(string $zipname, $status): void
    {
        if ($status !== true) {
            throw CannotOpenArchive::make(
                $zipname,
                $status
            );
        }
    }

    /**
     * Определить, произошла ли ошибка во время создания архива.
     * @param  string  $zipname
     * @param  mixed  $status
     * @return void
     *
     * @throws CannotCreateArchive
     */
    protected function assertArchiveIsCreated(string $zipname, $status): void
    {
        if ($status !== true) {
            throw CannotCreateArchive::make(
                $zipname,
                $status
            );
        }
    }

    /**
     * Определить, произошла ли ошибка во время извлечения файлов из архива.
     * @param  string  $zipname
     * @param  mixed  $status
     * @return void
     *
     * @throws UnableToExtractArchive
     */
    protected function assertArchiveIsExtracted(string $zipname, $status): void
    {
        if ($status !== true) {
            throw UnableToExtractArchive::make(
                $zipname,
                $status,
                $this->ziparchive->getStatusString()
            );
        }
    }

    /**
     * Определить, произошла ли ошибка при добавлении файла в архив.
     * @param  string  $zipname
     * @param  string  $filename
     * @param  mixed  $status
     * @return void
     *
     * @throws CannotAddFile
     */
    protected function assertFileIsAdded(string $zipname, string $filename, $status): void
    {
        if ($status !== true) {
            throw CannotAddFile::make(
                $zipname,
                $filename,
                $status,
                $this->ziparchive->getStatusString()
            );
        }
    }

    /**
     * Определить, произошла ли ошибка при добавлении пустой директории в архив.
     * @param  string  $zipname
     * @param  string  $dirname
     * @param  mixed  $status
     * @return void
     *
     * @throws CannotAddEmptyDirectory
     */
    protected function assertEmptyDirectoryIsAdded(string $zipname, string $dirname, $status): void
    {
        if ($status !== true) {
            throw CannotAddEmptyDirectory::make(
                $zipname,
                $dirname,
                $status,
                $this->ziparchive->getStatusString()
            );
        }
    }

    /**
     * Определить, произошла ли ошибка при удалении файла из архива.
     * @param  string  $zipname
     * @param  string  $filename
     * @param  mixed  $status
     * @return void
     *
     * @throws CannotDeleteElement
     */
    protected function assertFileIsDeleted(string $zipname, string $filename, $status): void
    {
        if ($status !== true) {
            throw CannotDeleteElement::make(
                $zipname,
                $filename,
                $status,
                $this->ziparchive->getStatusString()
            );
        }
    }

    /**
     * Определить, произошла ли ошибка во время закрытия архива.
     * @param  string|null  $zipname
     * @param  mixed  $status
     * @return void
     *
     * @throws RuntimeException|CannotCloseArchive
     */
    protected function assertArchiveIsClosed(?string $zipname, $status): void
    {
        // if (is_null($zipname) && $status === false) {
        //     throw new RuntimeException("Invalid or uninitialized Zip object.");
        // }

        if ($status !== true) {
            throw CannotCloseArchive::make(
                $zipname,
                $status,
                $this->ziparchive->getStatusString()
            );
        }
    }
}
