<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make\Service;

use Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make\Base;
use Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make\Service\Actions\ServiceCreate;
use Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make\Service\Actions\ServiceUpdate;
use Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make\Service\Actions\ServiceDelete;

class Service extends Base
{
    use ServiceCreate;
    use ServiceUpdate;
    use ServiceDelete;

    const ACTION_CREATE = 'ACTION_CREATE';
    const ACTION_UPDATE = 'ACTION_UPDATE';
    const ACTION_DELETE = 'ACTION_DELETE';

    protected $signature = 'mrzlanx532:make_service {--create} {--update} {--delete} {file_model_with_namespace} {file_service_with_namespace}';
    protected $description = 'Создаем частично заполненный сервис из таблицы';
    protected string $example = 'php artisan mrzlanx532:make_service --create \\App\\Models\\User \\App\\Http\\Resources\\UserCreateService';

    public function tryExecute()
    {
        $action = $this->detectAction();

        if (!$action) {
            return;
        }

        $fileContent = match ($action) {
            self::ACTION_CREATE => $this->getFileContentForServiceCreate(),
            self::ACTION_UPDATE => $this->getFileContentForServiceUpdate(),
            self::ACTION_DELETE => $this->getFileContentForServiceDelete(),
        };

        $this->saveFile($this->argument('file_service_with_namespace'), $fileContent);
    }

    protected function getSuccessMessage(): string
    {
        return 'Сервис `' . $this->argument('file_service_with_namespace') . '` успешно создан!';
    }

    private function detectAction()
    {
        if ($this->option('create')) {
            return self::ACTION_CREATE;
        }

        if ($this->option('update')) {
            return self::ACTION_UPDATE;
        }

        if ($this->option('delete')) {
            return self::ACTION_DELETE;
        }

        $this->warn('Вы не указали ни один из флагов: --create, --update, --delete');
    }

    private function getRuleByDataType($column)
    {
        if (in_array($column->DATA_TYPE, ['bigint', 'int'])) {
            return 'int';
        }

        if (in_array($column->DATA_TYPE, ['enum', 'text', 'varchar'])) {
            return 'string';
        }

        if ($column->COLUMN_TYPE === 'tinyint(1)') {
            return 'boolean';
        }

        if (in_array($column->DATA_TYPE, ['timestamp', 'date', 'datetime'])) {
            return 'date';
        }

        if ($column->DATA_TYPE === 'decimal') {
            return 'numeric';
        }
    }
}
