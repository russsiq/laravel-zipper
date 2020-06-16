## Класс-обертка для архиватора ZipArchive в Laravel 7.x.



Содержание:
 1. [Подключение](#Подключение)
 1. [Использование](#Использование)
     1. [Методы](#Методы)
     1. [Фасад `Zipper`](#facade-zipper)
 1. [Тестирование](#Тестирование)
 1. [Удаление пакета](#Удаление-пакета)
 1. [Лицензия](#Лицензия)

### Подключение

 - **1** Для добавления зависимости в проект на Laravel в файле `composer.json`

    ```json
    "require": {
        "russsiq/laravel-zipper": "dev-master"
    }
    ```

 - **2** Для подключения в уже созданный проект воспользуйтесь командной строкой:

    ```console
    composer require "russsiq/laravel-zipper:dev-master"
    ```

 - **3** Если в вашем приложении включен отказ от обнаружения пакетов в директиве `dont-discover` в разделе `extra` файла `composer.json`, то необходимо самостоятельно добавить в файле `config/app.php`:

    - **3.1** Провайдер услуг в раздел `providers`:

        ```php
        Russsiq\Zipper\ZipperServiceProvider::class,
        ```

    - **3.2** Псевдоним класса (Facade) в раздел `aliases`:

        ```php
        'Zipper' => Russsiq\Zipper\Facades\Zipper::class,
        ```

### Использование

<a name="facade-zipper"></a>
#### Фасад `Zipper`

Для инициализации класса-обертки `Zipper` вы можете воспользоваться одним из двух методов одноименного фасада `Zipper`:

```php
use Russsiq\Zipper\Facades\Zipper;

// Открытие существующего архива в формате `*.zip`.
$zipper = Zipper::open(string $filename);

// code ...

$zipper->close();
```

```php
use Russsiq\Zipper\Facades\Zipper;

// Создание нового архива в формате `*.zip`.
$zipper = Zipper::create(string $filename);

// code ...

$zipper->close();
```

#### Методы

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

### Тестирование

Для запуска тестов используйте команду:

```console
composer run-script test
```

Для запуска тестов под Windows 7 используйте команду:

```console
composer run-script test-win7
```

Для формирования agile-документации, генерируемой в HTML-формате и записываемой в файл [tests/testdox.html](tests/testdox.html), используйте команду:

```console
composer run-script testdox
```

### Удаление пакета

Для удаления пакета из вашего проекта на Laravel используйте команду:

```console
composer remove russsiq/laravel-zipper
```

### Лицензия

`laravel-zipper` – программное обеспечение с открытым исходным кодом, распространяющееся по лицензии [MIT](https://choosealicense.com/licenses/mit/).
