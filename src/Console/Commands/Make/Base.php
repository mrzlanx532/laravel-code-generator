<?php

namespace Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make;

use Mrzlanx532\LaravelCodeGenerator\Console\Exceptions\ForwardMessageToConsoleException;
use Mrzlanx532\LaravelCodeGenerator\Console\Exceptions\TableNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Throwable;

abstract class Base extends Command
{
    abstract public function tryExecute();
    abstract protected function getSuccessMessage(): string;

    /**
     * @throws Throwable
     */
    public function handle()
    {
        try {
            $this->tryExecute();
        } catch (Throwable $throwable) {
            if ($throwable instanceof ForwardMessageToConsoleException) {
                $this->error($throwable->getMessage());
                return;
            }

            throw $throwable;
        }

        $this->info($this->getSuccessMessage());
    }

    /**
     * @throws TableNotFoundException
     */
    protected function getColumns(): array
    {
        $databaseName = DB::connection()->getDatabaseName();

        try {
            $tableName = $this->argument('table_name');
        } catch (InvalidArgumentException) {
            $tableName = null;
        }

        if (!$tableName) {
            try {
                /* @var $model \Illuminate\Database\Eloquent\Model */
                $model = new ($this->argument('file_model_with_namespace'));
                $tableName = $model->getTable();

            } catch (Throwable) {
                $this->error('Failed to create instance from passed `model` argument');
                die(1);
            }
        }

        $columns = DB::select(<<<SQL
SELECT * FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = '$databaseName'
  AND TABLE_NAME = '$tableName';
SQL
);

        if (!$columns) {
            throw new TableNotFoundException('Таблица `' . $this->argument('table_name') . '` не существует в БД');
        }

        return $columns;
    }

    protected function getNamespaceOfFile($fileWithNamespace): string
    {
        $namespaceOfFile = substr($fileWithNamespace, 1);

        $explodedNamespace = explode('\\', $namespaceOfFile);

        $preparedString = '';

        foreach ($explodedNamespace as $partIndex => $partOfString) {

            if ($partIndex !== count($explodedNamespace) - 1) {

                if ($partIndex !== 0) {
                    $preparedString .= '\\';
                }
                $preparedString .= $partOfString;
            }
        }

        return $preparedString;
    }

    /**
     * Из: "\\App\\Http\\Resources\\UserResource"
     * Получаем: "/App/Http/Resources/UserResource"
     *
     * @param $fileWithNamespace
     * @return array|string
     */
    protected function replaceBackslashes($fileWithNamespace): array|string
    {
        $fileWithNamespace = str_replace('\\', '/', $fileWithNamespace);
        return str_replace('App', 'app', $fileWithNamespace);
    }

    /**
     * Из: "/App/Http/Resources/UserResource"
     * Получаем: "UserResource"
     *
     * @param $fileWithNamespace
     * @return string
     */
    protected function getClassNameFromFileWithNamespace($fileWithNamespace): string
    {
        $separatedString = explode('\\', $fileWithNamespace);
        $lastIndex = count($separatedString) - 1;

        return $separatedString[$lastIndex];
    }

    /**
     * Из: "/App/Http/Resources/UserResource"
     * Получаем: "App/Http/Resources/UserResource"
     *
     * @param $fileWithNamespace
     * @return string
     */
    protected function removeFirstBackslash($fileWithNamespace): string
    {
        return substr($fileWithNamespace, 1);
    }

    protected function saveFile($fileWithNamespace, $fileContent)
    {
        $fileWithNamespace = $this->replaceBackslashes($fileWithNamespace);
        $folderWithPathWithoutFile = $this->extractFolderWithPathForFile($fileWithNamespace);

        if (!is_dir($folderWithPathWithoutFile)) {

            mkdir($folderWithPathWithoutFile);
        }

        $filePath = base_path() . $this->replaceBackslashes($fileWithNamespace . '.php');

        file_put_contents($filePath, $fileContent);
    }

    /**
     * Из: "/app/Http/Resources/UserResource"
     * Получаем: "app/Http/Resources"
     *
     * @param $fileWithNamespace
     * @return string
     */
    protected function extractFolderWithPathForFile($fileWithNamespace): string
    {
        $partsOfFilePath = explode('/', $fileWithNamespace);
        $lastIndex = count($partsOfFilePath) - 1;

        $pathWithoutFile = '';

        foreach ($partsOfFilePath as $index => $part)
        {
            if ($index === 0) {
                continue;
            }

            if ($index !== 1) {
                $pathWithoutFile .= '/';
            }

            if ($index !== $lastIndex) {
                $pathWithoutFile .= $part;
            }
        }

        return $pathWithoutFile;
    }

    /**
     * Получаем запись "use Illuminate\\Support\\Carbon;\n",
     * если в колонках таблицы, есть даты
     *
     * @return string
     * @throws TableNotFoundException
     */
    protected function getUseCarbon(): string
    {
        $useCarbon = '';

        foreach ($this->getColumns() as $column)
        {
            if (in_array($column->DATA_TYPE, ['timestamp', 'date', 'datetime']))
            {
                $useCarbon .= 'use Illuminate\\Support\\Carbon;' . PHP_EOL;
                break;
            }
        }

        return $useCarbon;
    }
}
