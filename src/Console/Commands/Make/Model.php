<?php

namespace Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make;

use Mrzlanx532\LaravelCodeGenerator\Console\Exceptions\TableNotFoundException;

class Model extends Base
{
    protected $signature = 'mrzlanx532:make_model {table_name} {file_model_with_namespace}';
    protected $description = 'Создаем модель из таблицы и подставляем аннотации';
    protected string $example = 'php artisan Mrzlanx532:make_model market_offers_offers \\App\\Models\\Market\\Offer\\Offer';

    protected array $columns = [];
    protected bool $isExistsDeletedAt = false;

    /**
     * @throws TableNotFoundException
     */
    public function tryExecute()
    {
        $this->columns = $this->getColumns();

        foreach ($this->columns as $column) {
            if ($column->COLUMN_NAME === 'deleted_at') {
                $this->isExistsDeletedAt = true;
            }
        }

        $fileContent = $this->getFileContent();
        $this->saveFile($this->argument('file_model_with_namespace'), $fileContent);
    }

    protected function getSuccessMessage(): string
    {
        return 'Модель `' . $this->argument('file_model_with_namespace') . '` успешно создана!';
    }

    /**
     * @throws TableNotFoundException
     */
    private function getFileContent(): string
    {
        return implode('', [
            '<?php' . PHP_EOL . PHP_EOL,
            'namespace ' . $this->getNamespaceOfFile($this->argument('file_model_with_namespace')) . ';' . PHP_EOL . PHP_EOL,
            'use Illuminate\Database\Eloquent\Model;' . PHP_EOL,
            $this->getUseCarbon(),
            'use Illuminate\Database\Eloquent\Builder;' . PHP_EOL,
            $this->isExistsDeletedAt ? 'use Illuminate\Database\Eloquent\SoftDeletes;' . PHP_EOL . PHP_EOL : PHP_EOL,
            '/**' . PHP_EOL,
            ' * ' . $this->argument('file_model_with_namespace') . PHP_EOL,
            ' *' . PHP_EOL,
            $this->getDynamicAnnotations(),
            ' *' . PHP_EOL,
            ' * @method static Builder|' . $this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')) . ' query()' . PHP_EOL,
            ' * @method static ' . $this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')) . '|null find($id)' . PHP_EOL,
            ' * @method static ' . $this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')) . ' findOrFail($id)' . PHP_EOL,
            ' *' . PHP_EOL,
            ' * @mixin Model' . PHP_EOL,
            ' */' . PHP_EOL,
            'class ' . $this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')) . ' extends Model' . PHP_EOL,
            '{' . PHP_EOL,
            $this->getModelContent(),
            '}'
        ]);
    }

    /**
     * @return string
     */
    private function getDynamicAnnotations(): string
    {
        $fileContent = '';

        foreach ($this->columns as $column) {

            $annotationType = $this->getAnnotationType($column);
            $nullableString = $column->IS_NULLABLE === 'YES' ? '|null' : '';
            $annotationTypeWithNullable = $annotationType . $nullableString;

            $fileContent .= ' * @property ' . $annotationTypeWithNullable . ' ' . $column->COLUMN_NAME . PHP_EOL;
        }

        return $fileContent;
    }

    private function getAnnotationType($column)
    {
        if ($column->COLUMN_KEY === 'PRI') {
            return 'int';
        }

        if (in_array($column->DATA_TYPE, ['bigint', 'int'])) {
            return 'int';
        }

        if (in_array($column->DATA_TYPE, ['enum', 'text', 'varchar'])) {
            return 'string';
        }

        if ($column->COLUMN_TYPE === 'tinyint(1)') {
            return 'bool';
        }

        if (in_array($column->DATA_TYPE, ['timestamp', 'date', 'datetime'])) {
            return 'Carbon';
        }

        if ($column->DATA_TYPE === 'decimal') {
            return 'float';
        }
    }

    private function getModelContent(): string
    {
        return implode('', [
            $this->isExistsDeletedAt ? '    use SoftDeletes;' . PHP_EOL . PHP_EOL : '',
            '    protected $table = \'' . $this->argument('table_name') . '\';' . PHP_EOL,
            $this->getPropertiesForCreatedAtAndUpdatedAt()
        ]);
    }

    private function getPropertiesForCreatedAtAndUpdatedAt()
    {
        $createdAtExist = false;
        $updatedAtExist = false;

        foreach ($this->columns as $column) {

            if ($column->DATA_TYPE !== 'timestamp') {
                continue;
            }

            if ($column->COLUMN_NAME === 'created_at') {
                $createdAtExist = true;
            }

            if ($column->COLUMN_NAME === 'updated_at') {
                $updatedAtExist = true;
            }
        }

        if (!$createdAtExist && !$updatedAtExist) {
            return PHP_EOL . '    public $timestamps = false;' . PHP_EOL;
        }

        if ($createdAtExist && $updatedAtExist) {
            return '';
        }

        if ($createdAtExist) {
            return PHP_EOL . '    const UPDATED_AT = null;' . PHP_EOL;
        }

        if ($updatedAtExist) {
            return PHP_EOL . '    const CREATED_AT = null;' . PHP_EOL;
        }
    }
}
