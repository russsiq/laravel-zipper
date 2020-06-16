<?php declare(strict_types=1);

namespace Tests\Feature\Support;

// Тестируемый класс.
use Russsiq\Zipper\Support\Zipper;

// Исключения.
use Russsiq\Zipper\Exceptions\ZipperException;
use Russsiq\Zipper\Exceptions\CannotAddEmptyDirectory;
use Russsiq\Zipper\Exceptions\CannotAddFile;
use Russsiq\Zipper\Exceptions\CannotCloseArchive;
use Russsiq\Zipper\Exceptions\CannotCreateArchive;
use Russsiq\Zipper\Exceptions\CannotDeleteElement;
use Russsiq\Zipper\Exceptions\CannotOpenArchive;
use Russsiq\Zipper\Exceptions\UnableToExtractArchive;

// Базовые расширения PHP.
use Countable;
use SplFileInfo;
use ZipArchive;

// Сторонние зависимости.
use Illuminate\Filesystem\Filesystem;
use Russsiq\Zipper\Abstracts\AbstractZipper;
use Russsiq\Zipper\Contracts\ZipperContract;
use Symfony\Component\Finder\Finder;

// Библиотеки тестирования.
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Russsiq\Zipper\Support\Zipper
 *
 * @cmd phpunit tests\Feature\Support\ZipperTest.php
 */
class ZipperTest extends TestCase
{
    private const DUMMY_DIR = __DIR__.'/tmp';
    private const DUMMY_FILE = self::DUMMY_DIR.'/new-empty-file.zip';

    /**
     * Экземпляр класса по работе с файловой системой.
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Экземпляр класса по работе с архивами.
     * @var ZipArchive
     */
    private $ziparchive;

    /**
     * Экземпляр Класса-обертки для архиватора ZipArchive.
     * @var Zipper
     */
    private $zipper;

    /**
     * Этот метод вызывается перед запуском
     * первого теста этого класса тестирования.
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        // Очищаем кеш состояния файлов,
        // так как тестирование выполняется
        // на реальной файловой системе.
        clearstatcache();

        $directory = self::DUMMY_DIR;

        if (!is_dir($directory)) {
            mkdir($directory);
        }
    }

    /**
     * Этот метод вызывается перед каждым тестом.
     * @return void
     */
    protected function setUp(): void
    {
        if (!class_exists('ZipArchive', false)) {
            $this->markTestSkipped('Class [ZipArchive] not found.');
        }

        $this->assertDirectoryExists(self::DUMMY_DIR);
        $this->assertDirectoryIsWritable(self::DUMMY_DIR);

        $this->filesystem = Mockery::mock(new Filesystem);
        $this->ziparchive = new ZipArchive;

        $this->zipper = new Zipper(
            $this->filesystem,
            $this->ziparchive
        );
    }

    /**
     * Этот метод вызывается после каждого теста.
     * @return void
     */
    protected function tearDown(): void
    {
        // В некоторых тестах создаются файлы и ожидаются исключения.
        // Данный файл по завершению таких тестов, должен удаляться.
        if (is_file(self::DUMMY_FILE)) {
            unlink(self::DUMMY_FILE);
        }

        // Очищаем кеш состояния файлов,
        // так как тестирование выполняется
        // на реальной файловой системе.
        clearstatcache();

        Mockery::close();
    }

    /**
     * Этот метод вызывается после запуска
     * последнего теста этого класса тестирования.
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        if (is_dir(self::DUMMY_DIR)) {
            rmdir(self::DUMMY_DIR);
        }
    }

    /**
     * @test
     * @covers ::__construct
     *
     * Экземпляр Класса-обертки успешно создан.
     * @return void
     */
    public function testSuccessfullyInitiated(): void
    {
        // $this->expectExceptionMessage("Invalid or uninitialized Zip object.");
        $this->assertInstanceOf(ZipperContract::class, $this->zipper);
    }

    // /**
    //  * @test
    //  * @covers ::__destruct
    //  *
    //  * Экземпляр Класса-обертки успешно удален.
    //  * @return void
    //  */
    // public function testSuccessfullyTerminated(): void
    // {
    //     //
    // }

    /**
     * @test
     * @covers ::count
     *
     * Получить количество файлов в архиве.
     * @return void
     */
    public function testGetCountFilesInArchive(): void
    {
        $this->assertSame(0, $this->zipper->count());
    }

    /**
     * @test
     * @covers ::filename
     *
     * Получить полный путь, включая имя, текущего рабочего архива.
     * @return void
     */
    public function testGetFilenameArchive(): void
    {
        $this->assertNull($this->zipper->filename());
    }

    /**
     * @test
     * @covers ::open
     *
     * Выбрасывание исключения при открытии несуществующего архива.
     * @return void
     */
    public function testThrowsExceptionWhenOpeningNonexistentArchive(): void
    {
        // Убедимся, что файл архива действительно не существует.
        $filePath = $this->createNewEmptyArchiveFile(false);
        $this->assertFileDoesNotExist($filePath);

        // Ожидаем исключение, оповещающее о несуществующем архиве.
        $this->expectException(CannotOpenArchive::class);
        $this->expectExceptionMessage(
            CannotOpenArchive::getErrorFromStatus(ZipArchive::ER_NOENT)
        );

        // Попытаемся открыть несуществующий архив.
        $ziparchive = $this->zipper->open($filePath);
    }

    /**
     * @test
     * @covers ::open
     *
     * Успешное открытие и закрытие существующего пустого файла.
     * @return void
     */
    public function testSuccessfullyOpeningAndClosingAnExistingEmptyFile(): void
    {
        // Перед проверкой существования файла создадим его.
        $filePath = $this->createNewEmptyArchiveFile(true);

        // Убедимся, что файл архива действительно существует.
        $this->assertFileExists($filePath);

        // Попытаемся открыть существующий архив.
        $ziparchive = $this->zipper->open($filePath);

        // В открытом архиве не должно быть файлов.
        $this->assertSame(0, $ziparchive->count());

        // Архиватор должен выдавать корректный путь к текущему архиву.
        $this->assertSame(realpath($filePath), $ziparchive->filename());

        // Закрываем пустой архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  2) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  3) файл пустого архива должен сохраняться после закрытия
        $this->assertFileExists($filePath);
    }

    /**
     * @test
     * @covers ::create
     *
     * Выбрасывание исключения при создании новый архив, когда файл уже существует.
     * @return void
     */
    public function testThrowsExceptionOnCreateNewArchiveWhenFileAlreadyExists()
    {
        // Перед проверкой существования файла создадим его.
        $filePath = $this->createNewEmptyArchiveFile(true);

        // Убедимся, что файл архива действительно существует.
        $this->assertFileExists($filePath);

        // Ожидаем исключение, оповещающее о уже существующем архиве.
        $this->expectException(CannotCreateArchive::class);
        $this->expectExceptionMessage(
            CannotCreateArchive::getErrorFromStatus(ZipArchive::ER_EXISTS)
        );

        // Попытаемся создать архив при существующем файле архива.
        $ziparchive = $this->zipper->create($filePath);
    }

    /**
     * @test
     * @covers ::create
     *
     * Создать новый пустой файл архива без последующего физического сохранения.
     * @return void
     */
    public function testCreateNewEmptyArchiveFileWithoutFurtherPhysicalSaving(): void
    {
        // Убедимся, что файл архива действительно не существует.
        $filePath = $this->createNewEmptyArchiveFile(false);
        $this->assertFileDoesNotExist($filePath);

        // Попытаемся создать новый архив.
        $ziparchive = $this->zipper->create($filePath);

        // В файловой системе не должно быть доступа к файлу архива.
        // Это снижает вероятность одновременного доступа к нему.
        $filePath = $ziparchive->filename();
        $this->assertFalse(realpath($filePath));
        $this->assertFileDoesNotExist($filePath);

        // Закрываем пустой архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  2) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  3) файл пустого архива не должен сохраняться после закрытия
        $this->assertFileDoesNotExist($filePath);
    }

    /**
     * @test
     * @covers ::addFromString
     *
     * Создать новый файл архива с добавлением файла из строки.
     * @return void
     */
    public function testCreateNewArchiveFileWithAdditionFileFromString(): void
    {
        // Убедимся, что файл архива действительно не существует.
        $filePath = $this->createNewEmptyArchiveFile(false);
        $this->assertFileDoesNotExist($filePath);

        // Попытаемся создать новый архив.
        $ziparchive = $this->zipper->create($filePath);

        // В файловой системе не должно быть доступа к файлу архива.
        // Это снижает вероятность одновременного доступа к нему.
        $filePath = $ziparchive->filename();
        $this->assertFalse(realpath($filePath));
        $this->assertFileDoesNotExist($filePath);

        // Добавляем новый файл с содержимым из строки.
        $additionFile = 'dummy.txt';
        $ziparchive->addFromString($additionFile, 'dummy contents');

        // Закрываем новый архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  2) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  3) файл нового архива должен присутствовать в файловой системе после закрытия
        $this->assertFileExists($filePath);
    }

    /**
     * @test
     * @covers ::addFile
     *
     * Создать новый файл архива с добавлением физического файла.
     * @return void
     */
    public function testCreateNewArchiveFileWithAdditionPhysicalFile(): void
    {
        // Убедимся, что файл архива действительно не существует.
        $filePath = $this->createNewEmptyArchiveFile(false);
        $this->assertFileDoesNotExist($filePath);

        // Попытаемся создать новый архив.
        $ziparchive = $this->zipper->create($filePath);

        // В файловой системе не должно быть доступа к файлу архива.
        // Это снижает вероятность одновременного доступа к нему.
        $filePath = $ziparchive->filename();
        $this->assertFalse(realpath($filePath));
        $this->assertFileDoesNotExist($filePath);

        // Предварительно создаем новый файл.
        $additionFile = self::DUMMY_DIR.'/dummy.txt';
        file_put_contents($additionFile, 'dummy contents', LOCK_EX);

        // Добавляем новый файл.
        $ziparchive->addFile($additionFile);

        // Перед закрытием архива, архиватор должен:
        //  1) показывать присутствие добавленного файла
        $this->assertSame(1, $ziparchive->count());

        // Закрываем новый архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  2) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  3) файл нового архива должен присутствовать в файловой системе после закрытия
        $this->assertFileExists($filePath);

        unlink($additionFile);
    }

    /**
     * @test
     * @covers ::addDirectory
     *
     * Создать новый файл архива с добавлением директории.
     * @return void
     */
    public function testCreateNewArchiveFileWithAdditionDirectory(): void
    {
        // Убедимся, что файл архива действительно не существует.
        $filePath = $this->createNewEmptyArchiveFile(false);
        $this->assertFileDoesNotExist($filePath);

        // Попытаемся создать новый архив.
        $ziparchive = $this->zipper->create($filePath);

        // В файловой системе не должно быть доступа к файлу архива.
        // Это снижает вероятность одновременного доступа к нему.
        $filePath = $ziparchive->filename();
        $this->assertFalse(realpath($filePath));
        $this->assertFileDoesNotExist($filePath);

        // Предварительно создаем новый файл в поддиректории.
        $subDirectory = self::DUMMY_DIR.'/sub-dir';
        mkdir($subDirectory);
        $additionFile = $subDirectory.'/dummy.txt';
        file_put_contents($additionFile, 'dummy contents', LOCK_EX);

        // Добавляем новую директорию.
        $ziparchive->addDirectory($subDirectory, basename($subDirectory));

        // Перед закрытием архива, архиватор должен:
        //  1) показывать присутствие добавленного файла
        $this->assertSame(1, $ziparchive->count());

        // Закрываем новый архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  2) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  3) файл нового архива должен присутствовать в файловой системе после закрытия
        $this->assertFileExists($filePath);

        // Удаляем предварительно созданный файл.
        unlink($additionFile);
        // ... и его родительскую директорию
        rmdir($subDirectory);
    }

    /**
     * @test
     * @covers ::addEmptyDirectory
     *
     * Создать новый файл архива с добавлением пустой директории.
     * @return void
     */
    public function testCreateNewArchiveFileWithAdditionEmptyDirectory(): void
    {
        // Убедимся, что файл архива действительно не существует.
        $filePath = $this->createNewEmptyArchiveFile(false);
        $this->assertFileDoesNotExist($filePath);

        // Попытаемся создать новый архив.
        $ziparchive = $this->zipper->create($filePath);

        // В файловой системе не должно быть доступа к файлу архива.
        // Это снижает вероятность одновременного доступа к нему.
        $filePath = $ziparchive->filename();
        $this->assertFalse(realpath($filePath));
        $this->assertFileDoesNotExist($filePath);

        // Добавляем новую поддиректорию.
        $ziparchive->addEmptyDirectory($subDirectory = 'sub-dir');

        // Перед закрытием архива, архиватор должен:
        //  1) показывать присутствие добавленной директории
        $this->assertSame(1, $ziparchive->count());

        // Закрываем новый архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  2) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  3) файл нового архива должен присутствовать в файловой системе после закрытия
        $this->assertFileExists($filePath);
    }

    /**
     * @test
     * @covers ::deleteFile
     *
     * Удалить файл из архива, используя его имя.
     * @return void
     */
    public function testDeleteFileFromArchiveUsingItsName(): void
    {
        // Убедимся, что файл архива действительно не существует.
        $filePath = $this->createNewEmptyArchiveFile(false);
        $this->assertFileDoesNotExist($filePath);

        // Попытаемся создать новый архив.
        $ziparchive = $this->zipper->create($filePath);

        // В файловой системе не должно быть доступа к файлу архива.
        // Это снижает вероятность одновременного доступа к нему.
        $filePath = $ziparchive->filename();
        $this->assertFalse(realpath($filePath));
        $this->assertFileDoesNotExist($filePath);

        // Добавляем новый файл с содержимым из строки.
        $additionFile = 'dummy.txt';
        $ziparchive->addFromString($additionFile, 'dummy contents');

        // Закрываем новый архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  2) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  3) файл нового архива должен присутствовать в файловой системе после закрытия
        $this->assertFileExists($filePath);

        // Попытаемся открыть существующий архив.
        $ziparchive = $this->zipper->open($filePath);

        // В открытом архиве должен быть 1 файл.
        $this->assertSame(1, $ziparchive->count());

        // Архиватор должен выдавать корректный путь к текущему архиву.
        $this->assertSame(realpath($filePath), $ziparchive->filename());

        // Удаляем файл из архива.
        $ziparchive->deleteFile($additionFile);

        // В открытом архиве по-прежнему должен быть 1 файл.
        // Только операция `close` сохраняет принятые изменения.
        $this->assertSame(1, $ziparchive->count());

        // Закрываем пустой архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  2) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  3) файл пустого архива не должен сохраняться после закрытия
        $this->assertFileDoesNotExist($filePath);
    }

    /**
     * @test
     * @covers ::deleteDirectory
     *
     * Удалить директорию из архива, используя ее имя.
     * @return void
     */
    public function testDeleteDirectoryFromArchiveUsingItsName(): void
    {
        // Убедимся, что файл архива действительно не существует.
        $filePath = $this->createNewEmptyArchiveFile(false);
        $this->assertFileDoesNotExist($filePath);

        // Попытаемся создать новый архив.
        $ziparchive = $this->zipper->create($filePath);

        // В файловой системе не должно быть доступа к файлу архива.
        // Это снижает вероятность одновременного доступа к нему.
        $filePath = $ziparchive->filename();
        $this->assertFalse(realpath($filePath));
        $this->assertFileDoesNotExist($filePath);

        // Добавляем новую поддиректорию.
        $subDirectory = 'sub-dir';
        $ziparchive->addEmptyDirectory($subDirectory);

        // Добавляем новый файл с содержимым из строки.
        $additionFile = $subDirectory.'/dummy.txt';
        $ziparchive->addFromString($additionFile, 'dummy contents');

        // Закрываем новый архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  2) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  3) файл нового архива должен присутствовать в файловой системе после закрытия
        $this->assertFileExists($filePath);

        // Попытаемся открыть существующий архив.
        $ziparchive = $this->zipper->open($filePath);

        // В открытом архиве должно быть 2 элемента. А почему?
        $this->assertSame(2, $ziparchive->count());

        // Архиватор должен выдавать корректный путь к текущему архиву.
        $this->assertSame(realpath($filePath), $ziparchive->filename());

        // Удаляем директорию из архива.
        $ziparchive->deleteDirectory($subDirectory);

        // В открытом архиве по-прежнему должен быть 2 элемента.
        // Только операция `close` сохраняет принятые изменения.
        $this->assertSame(2, $ziparchive->count());

        // Закрываем пустой архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  2) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  3) файл пустого архива не должен сохраняться после закрытия
        $this->assertFileDoesNotExist($filePath);
        $this->assertFalse(realpath($filePath));
    }

    /**
     * @test
     * @covers ::extractTo
     *
     * Извлечь весь архив в указанное место назначения.
     * @return void
     */
    public function testExtractArchiveContents(): void
    {
        // Убедимся, что файл архива действительно не существует.
        $filePath = $this->createNewEmptyArchiveFile(false);
        $this->assertFileDoesNotExist($filePath);

        // Попытаемся создать новый архив.
        $ziparchive = $this->zipper->create($filePath);

        // В файловой системе не должно быть доступа к файлу архива.
        // Это снижает вероятность одновременного доступа к нему.
        $filePath = $ziparchive->filename();
        $this->assertFalse(realpath($filePath));
        $this->assertFileDoesNotExist($filePath);

        // Добавляем новую поддиректорию.
        $subDirectory = 'sub-dir';
        $ziparchive->addEmptyDirectory($subDirectory);

        // Добавляем новый файл с содержимым из строки.
        $additionFile = $subDirectory.'/dummy.txt';
        $ziparchive->addFromString($additionFile, 'dummy contents');

        // Закрываем новый архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  2) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  3) файл нового архива должен присутствовать в файловой системе после закрытия
        $this->assertFileExists($filePath);

        // Попытаемся открыть существующий архив.
        $ziparchive = $this->zipper->open($filePath);

        // В открытом архиве должно быть 2 элемента. А почему?
        $this->assertSame(2, $ziparchive->count());

        // Архиватор должен выдавать корректный путь к текущему архиву.
        $this->assertSame(realpath($filePath), $ziparchive->filename());

        // Извлекаем содержимое архива.
        $destination = self::DUMMY_DIR.'/extracted';
        $ziparchive->extractTo($destination);

        // В открытом архиве по-прежнему должен быть 2 элемента.
        $this->assertSame(2, $ziparchive->count());

        // Закрываем архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  2) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  3) файл архива должен сохраняться после закрытия
        $this->assertFileExists($filePath);

        // Проверяем иерархию извлеченных файлов.
        $this->assertDirectoryExists($destination);
        $this->assertDirectoryExists($destination.'/'.$subDirectory);
        $this->assertFileExists($destination.'/'.$additionFile);

        // Удаляем извлеченное содержимое архива.
        unlink($destination.'/'.$additionFile);
        rmdir($destination.'/'.$subDirectory);
        rmdir($destination);
    }

    /**
     * @test
     * @covers ::extractTo
     *
     * Извлечь содержимое архива с конкретными записями в указанное место назначения.
     * @return void
     */
    public function testExtractArchiveContentsWithSpecificEntries(): void
    {
        // Убедимся, что файл архива действительно не существует.
        $filePath = $this->createNewEmptyArchiveFile(false);
        $this->assertFileDoesNotExist($filePath);

        // Попытаемся создать новый архив.
        $ziparchive = $this->zipper->create($filePath);

        // В файловой системе не должно быть доступа к файлу архива.
        // Это снижает вероятность одновременного доступа к нему.
        $filePath = $ziparchive->filename();
        $this->assertFalse(realpath($filePath));
        $this->assertFileDoesNotExist($filePath);

        // Добавляем новую поддиректорию.
        $subDirectory = 'sub-dir';
        // $ziparchive->addEmptyDirectory($subDirectory);

        // Добавляем новый файл с содержимым из строки.
        $additionFiles[] = $subDirectory.'/dummy.txt';
        $additionFiles[] = $subDirectory.'/other-dummy.txt';
        $additionFiles[] = $subDirectory.'/another-dummy.txt';

        array_map(function (string $filename) use ($ziparchive) {
            $ziparchive->addFromString($filename, $filename);
        }, $additionFiles);

        // Закрываем новый архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  2) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  3) файл нового архива должен присутствовать в файловой системе после закрытия
        $this->assertFileExists($filePath);

        // Попытаемся открыть существующий архив.
        $ziparchive = $this->zipper->open($filePath);

        // В открытом архиве должно быть 4 элемента.
        $this->assertSame(count($additionFiles), $ziparchive->count());

        // Архиватор должен выдавать корректный путь к текущему архиву.
        $this->assertSame(realpath($filePath), $ziparchive->filename());

        // Извлекаем содержимое архива.
        $extractingFile = $additionFiles[0];
        $destination = self::DUMMY_DIR.'/extracted';
        $ziparchive->extractTo($destination, [
            $extractingFile
        ]);

        // В открытом архиве по-прежнему должен быть 4 элемента.
        $this->assertSame(count($additionFiles), $ziparchive->count());

        // Закрываем архив.
        $ziparchive->close();

        // После закрытия архива, архиватор должен:
        //  1) показывать отсутствие файлов
        $this->assertSame(0, $ziparchive->count());

        //  2) выдавать пустой путь
        $this->assertNull($ziparchive->filename());

        //  3) файл архива должен сохраняться после закрытия
        $this->assertFileExists($filePath);

        // Проверяем иерархию извлеченных файлов.
        $this->assertDirectoryExists($destination);
        $this->assertDirectoryExists($destination.'/'.$subDirectory);
        $this->assertFileExists($destination.'/'.$extractingFile);

        // Удаляем извлеченное содержимое архива.
        unlink($destination.'/'.$extractingFile);
        rmdir($destination.'/'.$subDirectory);
        rmdir($destination);
    }

    /**
     * Создать новый пустой файл архива.
     * @param  bool  $isReadable  Маркер физического присутствия файла.
     * @return string  Полный путь к созданному файлу.
     */
    protected function createNewEmptyArchiveFile(bool $isReadable = false): string
    {
        $filePath = self::DUMMY_FILE;

        if ($isReadable) {
            // file_put_contents($filePath, '', LOCK_EX);
            file_put_contents($filePath, base64_decode('UEsFBgAAAAAAAAAAAAAAAAAAAAAAAA=='), LOCK_EX);
        }

        return $filePath;
    }
}
