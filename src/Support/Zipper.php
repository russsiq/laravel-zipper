<?php

namespace Russsiq\Zipper\Support;

// Базовые расширения PHP.
use SplFileInfo;
use ZipArchive;

// Сторонние зависимости.
use Russsiq\Zipper\Abstracts\AbstractZipper;
use Russsiq\Zipper\Contracts\ZipperContract;
use Symfony\Component\Finder\Finder;

/**
 * Класс-обертка для архиватора ZipArchive.
 */
class Zipper extends AbstractZipper
{
    /**
     * Получить количество файлов в архиве.
     * @return int
     */
    public function count(): int
    {
        return $this->ziparchive->numFiles;

        // ZipArchive::count() is introduced in PHP 7.2.
        // You can use $zip->numFiles if you are on PHP <7.2
    }

    /**
     * Получить полный путь, включая имя, текущего рабочего архива.
     * @return string|null
     */
    public function filename(): ?string
    {
        return $this->ziparchive->filename ?: null;
    }

    /**
     * Открыть архив для последующей работы с ним (для чтения, записи или изменения).
     * @param  string  $filename
     * @return self
     */
    public function open(string $filename): ZipperContract
    {
        // Возвращает TRUE при успешном завершении или код ошибки.
        $status = $this->ziparchive->open($filename, ZipArchive::CHECKCONS);

        return $this->assertArchiveIsOpened($filename, $status) ?: $this;
    }

    /**
     * Создать архив для последующей работы с ним (для чтения, записи или изменения).
     * @param  string  $filename
     * @return self
     */
    public function create(string $filename): ZipperContract
    {
        // Возвращает TRUE при успешном завершении или код ошибки.
        $status = $this->ziparchive->open($filename, ZipArchive::CREATE | ZipArchive::EXCL);

        return $this->assertArchiveIsCreated($filename, $status) ?: $this;
    }

    /**
     * Извлечь весь архив или его части в указанное место назначения.
     * @param  string  $destination  Место назначение, куда извлекать файлы.
     * @param  array|null  $entries  Массив элементов для извлечения.
     * @return bool
     */
    public function extractTo(string $destination, array $entries = null): bool
    {
        // Возвращает TRUE в случае успешного завершения
        // или FALSE в случае возникновения ошибки.
        $status = $this->ziparchive->extractTo($destination, $entries);

        return $this->assertArchiveIsExtracted($this->filename(), $status) ?: $status;
    }

    /**
     * Добавить в архив файл, используя содержимое строки.
     * @param  string  $localname  Относительный путь к файлу в архиве, включая его имя.
     * @param  string  $contents  Содержимое для создания файла. Используется в двоичном безопасном режиме.
     * @return bool
     */
    public function addFromString(string $localname, string $contents) : bool
    {
        // Возвращает TRUE в случае успешного завершения
        // или FALSE в случае возникновения ошибки.
        $status = $this->ziparchive->addFromString($localname, $contents);

        return $this->assertFileIsAdded($this->filename(), $localname, $status) ?: $status;
    }

    /**
     * Добавить в архив файл по указанному пути.
     * @param  string  $filename  Абсолютный путь добавляемого файла.
     * @param  string|null  $localname  Относительный путь к файлу в архиве, включая его имя.
     * @return bool
     */
    public function addFile(string $filename, string $localname = null) : bool
    {
        $filename = $this->normalizePath($filename);

        // Возвращает TRUE в случае успешного завершения
        // или FALSE в случае возникновения ошибки.
        $status = $this->ziparchive->addFile($filename, $localname);

        return $this->assertFileIsAdded($this->filename(), $filename, $status) ?: $status;
    }

    /**
     * Добавить в архив директорию.
     * @param  string  $realPath
     * @param  string  $relativePath
     * @return bool
     */
    public function addDirectory(string $realPath, string $relativePath): bool
    {
        $finder = $this->createFinder($realPath)
            ->ignoreDotFiles(false)
            ->ignoreVCS(false);

        // Исключаем файлы из списка архивируемых,
        // доступ к которым возможен через ссылки.
        $exluded = [];

        foreach ($finder->getIterator() as $file) {
            // На Windows 7 некорректная работа со считыванием информации о ссылках.
            if ($file->isLink() or (! in_array($file->getType(), ['file', 'dir']))) {
                // А вот и причина ошибки, из-за которой метод `exclude` неверно отрабатывал.
                // https://github.com/symfony/finder/blob/008b6cc6da7141baf1766d72d2731b0e6f78b45b/Iterator/ExcludeDirectoryFilterIterator.php#L37
                // https://github.com/russsiq/laravel-assistant/blob/7b36b4a1a56c4364b470db3188c06eb1871ed8f7/src/app/Support/Updater/AbstractUpdater.php#L166
                $exluded[] = $this->normalizePath($file->getRelativePathname());
            }
        }

        $files = $finder->exclude($exluded)->files();

        foreach ($files as $file) {
            $this->addFile(
                $file->getRealPath(),
                $relativePath.DIRECTORY_SEPARATOR.$file->getRelativePathname()
            );
        }

        return true;
    }

    /**
     * Добавить в архив пустую директорию.
     * @param  string  $dirname
     * @return bool
     */
    public function addEmptyDirectory(string $dirname): bool
    {
        // Возвращает TRUE в случае успешного завершения
        // или FALSE в случае возникновения ошибки.
        $status = $this->ziparchive->addEmptyDir($dirname);

        return $this->assertEmptyDirectoryIsAdded($this->filename(), $dirname, $status) ?: $status;
    }

    /**
     * Удалить элемент (файл) из архива, используя его имя.
     * @param  string  $filename
     * @return bool
     */
    public function deleteFile(string $filename): bool
    {
        // Возвращает TRUE в случае успешного завершения
        // или FALSE в случае возникновения ошибки.
        $status = $this->ziparchive->deleteName($filename);

        return $this->assertFileIsDeleted($this->filename(), $filename, $status) ?: $status;
    }

    /**
     * Удалить элемент (каталог) из архива, используя его имя.
     * @param  string  $dirname
     * @param  bool  $force
     * @return bool
     *
     * @TODO Разобраться с `$force`
     */
    public function deleteDirectory(string $dirname, bool $force = false): bool
    {
        // Архиватор обрабатывает директории, которые оканчиваются на `/`.
        $dirname = rtrim($dirname, '/\\');
        $dirname = $this->normalizePath($dirname);
        $dirname = $dirname.'/';

        // // Попытаемся напрямую удалить директорию.
        // if (true === $this->ziparchive->deleteName($dirname)) {
        //     return true;
        // }

        // Если напрямую не получилось, то это значит, что директория непустая.
        // Архиватор не позволяет удалять непустые директории.
        for ($i = 0; $i < $this->count(); $i++) {
            $nameByIndex = $this->ziparchive->getNameIndex($i);
            $filename = $this->normalizePath($nameByIndex);

            // Пропускаем искомую директорию и элементы,
            // которые не расположены в искомой директории.
            if (substr($filename, 0, strlen($dirname)) === $dirname) {
                $this->deleteFile($nameByIndex);
            }
        }

        // Пока оставим как есть.
        $this->ziparchive->deleteName($dirname);

        return true;
    }

    /**
     * Закрыть текущий (открытый или созданный) архив и сохранить изменения.
     * @return bool
     */
    public function close(): bool
    {
        $status = false;

        // Перед закрытием архива нужно сохранить его имя.
        // Если при закрытии будет ошибка, то используем его имя
        // как маркер того, что вообще он создавался / открывался ли.
        if ($filename = $this->filename()) {
            // Возвращает TRUE в случае успешного завершения
            // или FALSE в случае возникновения ошибки.
            $status = $this->ziparchive->close();
        }

        return $this->assertArchiveIsClosed($filename, $status) ?: $status;
    }

    /**
     * Создать экземпляр Поисковика.
     * @param  mixed  $directories
     * @return Finder
     */
    protected function createFinder($directories): Finder
    {
        return Finder::create()->in((array) $directories);
    }

    /**
     * Нормализовать путь. Для максимальной переносимости,
     * рекомендуется всегда использовать прямые слеши `/`
     * в качестве разделителя директорий в именах файлов.
     * @param  string  $path
     * @return string
     */
    protected function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
