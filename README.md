# Класс-обертка для архиватора ZipArchive в Laravel 9.x.

## Подключение

Для добавления зависимости в проект на Laravel, используйте менеджер пакетов Composer:

```console
composer require russsiq/laravel-zipper
```

Если в вашем приложении включен отказ от обнаружения пакетов в директиве `dont-discover` в разделе `extra` файла `composer.json`, то необходимо самостоятельно добавить следующее в файле `config/app.php`:

- Провайдер услуг в раздел `providers`:

```php
Russsiq\Zipper\ZipperServiceProvider::class,
```

- Псевдоним класса (Facade) в раздел `aliases`:

```php
'Zipper' => Russsiq\Zipper\Facades\Zipper::class,
```

## Использование

### Методы

Все публичные методы доступны через фасад `Zipper`:

```php
Zipper::someMethod(example $someParam);
```

Список доступных публичных методов класса-обертки `Zipper`:

 - [addDirectory](#method-addDirectory)
 - [addEmptyDirectory](#method-addEmptyDirectory)
 - [addFile](#method-addFile)
 - [addFromString](#method-addFromString)
 - [close](#method-close)
 - [create](#method-create)
 - [deleteDirectory](#method-deleteDirectory)
 - [deleteFile](#method-deleteFile)
 - [extractTo](#method-extractTo)
 - [filename](#method-filename)
 - [open](#method-open)

<a name="method-addDirectory"></a>
##### `addDirectory(string $realPath, string $relativePath): bool`
Добавить в архив директорию.

<a name="method-addEmptyDirectory"></a>
##### `addEmptyDirectory(string $dirname): bool`
Добавить в архив пустую директорию.

<a name="method-addFile"></a>
##### `addFile(string $filename, string $localname = null): bool`
Добавить в архив файл по указанному пути.

<a name="method-addFromString"></a>
##### `addFromString(string $localname, string $contents) : bool`
Добавить в архив файл, используя содержимое строки.

<a name="method-close"></a>
##### `close(): bool`
Закрыть текущий (открытый или созданный) архив и сохранить изменения.

<a name="method-create"></a>
##### `create(string $filename): self`
Создать архив для последующей работы с ним (для чтения, записи или изменения).

<a name="method-deleteDirectory"></a>
##### `deleteDirectory(string $dirname): bool`
Удалить элемент (каталог) из архива, используя его имя.

<a name="method-deleteFile"></a>
##### `deleteFile(string $filename): bool`
Удалить элемент (файл) из архива, используя его имя.

<a name="method-extractTo"></a>
##### `extractTo(string $destination, array $entries = null): bool`
Извлечь весь архив или его части в указанное место назначения.

<a name="method-filename"></a>
##### `filename(): ?string`
Получить полный путь, включая имя, текущего рабочего архива.

<a name="method-open"></a>
##### `open(string $filename): self`
Открыть архив для последующей работы с ним (для чтения, записи или изменения).

### Пример использования

Для инициализации класса-обертки `Zipper` вы можете воспользоваться одним из двух методов одноименного фасада `Zipper`:

```php
use Russsiq\Zipper\Facades\Zipper;

// Полный путь к создаваемому архиву.
$filename = \storage_path('/tmp/new-ziparchive.zip');

// Класс-обертка выбросит исключение,
// при попытки перезаписи существующего файла.
if (!\file_exists($filename)) {
    // Создание нового архива в формате `*.zip`.
    $zipper = Zipper::create($filename);

    // Добавление нового файла в архив из содержимого строки.
    $zipper->addFromString('new-file.txt', 'dummy contents');

    // Закрытие архива для принятия внесенных изменений.
    $zipper->close();
}
```

```php
use Russsiq\Zipper\Facades\Zipper;

// Полный путь к открываемому архиву.
$filename = \storage_path('/tmp/exists-ziparchive.zip');

// Полный путь назначения для извлечения содержимого архива.
$destination = \storage_path('/tmp/extracted');

// Класс-обертка выбросит исключение,
// при попытки открытия несуществующего файла архива.
if (\file_exists($filename)) {
    // Открытие существующего архива в формате `*.zip`.
    $zipper = Zipper::open($filename);

    // Извлечение всего содержимого из файла архива.
    $zipper->extractTo($destination);

    // Закрытие архива для принятия внесенных изменений.
    $zipper->close();
}
```

## Тестирование

Для запуска тестов используйте команду:

```console
composer run-script test
```

Для запуска тестов и формирования agile-документации, генерируемой в HTML-формате и записываемой в файл [tests/testdox.html](tests/testdox.html), используйте команду:

```console
composer run-script testdox
```

## Удаление пакета

Для удаления пакета из вашего проекта на Laravel используйте команду:

```console
composer remove russsiq/laravel-zipper
```

## Лицензия

`laravel-zipper` – программное обеспечение с открытым исходным кодом, распространяющееся по лицензии [MIT](LICENSE).
