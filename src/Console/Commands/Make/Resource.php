<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make;

use Mrzlanx532\LaravelCodeGenerator\Console\Exceptions\TableNotFoundException;

class Resource extends Base
{
    protected $signature = 'mrzlanx532:make_resource {file_model_with_namespace} {file_resource_with_namespace}';
    protected $description = 'Создаем заполненный ресурс из таблицы';
    protected string $example = 'php artisan mrzlanx532:make_resource \\App\\Models\\User \\App\\Http\\Resources\\UserResource';

    public function tryExecute()
    {
        $fileContent = $this->getFileContent();
        $this->saveFile($this->argument('file_resource_with_namespace'), $fileContent);
    }

    protected function getSuccessMessage(): string
    {
        return 'Ресурс `' . $this->argument('file_resource_with_namespace') . '` успешно создан!';
    }

    /**
     * @throws TableNotFoundException
     */
    private function getFileContent(): string
    {
        return implode('', [
            '<?php' . PHP_EOL . PHP_EOL,
            'namespace ' . $this->getNamespaceOfFile($this->argument('file_resource_with_namespace')) . ';' . PHP_EOL . PHP_EOL,
            'use Illuminate\Http\Resources\Json\JsonResource;' . PHP_EOL,
            'use ' . $this->removeFirstBackslash($this->argument('file_model_with_namespace')) . ';' . PHP_EOL,
            $this->getUseCarbon() . PHP_EOL,
            'class ' . $this->getClassNameFromFileWithNamespace($this->argument('file_resource_with_namespace')) . ' extends JsonResource' . PHP_EOL,
            '{' . PHP_EOL,
            '    /* @var $resource ' . $this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')) . ' */' . PHP_EOL,
            '    public $resource;' . PHP_EOL . PHP_EOL,
            '    public function toArray($request): array' . PHP_EOL,
            '    {' . PHP_EOL,
            '        return [' . PHP_EOL,
            $this->getToArrayData(),
            '        ];' . PHP_EOL,
            '    }' . PHP_EOL,
            '}'
        ]);
    }

    /**
     * @throws TableNotFoundException
     */
    private function getToArrayData(): string
    {
        $fileContent = '';

        foreach ($this->getColumns() as $column) {
            $fileContent .= '            ';
            $fileContent .= '\'' . $column->COLUMN_NAME . '\' => ' . $this->getApproachOfGettingColumnContent($column) . PHP_EOL;
        }

        return $fileContent;
    }

    private function getApproachOfGettingColumnContent($column): string
    {
        $approachOfGettingColumnContent = '$this->resource->' . $column->COLUMN_NAME;

        if (in_array($column->DATA_TYPE, ['timestamp', 'date', 'datetime'])) {
            $approachOfGettingColumnContent .= ' ? Carbon::parse($this->resource->' . $column->COLUMN_NAME . ')->timestamp : null';
        }

        $approachOfGettingColumnContent .= ',';

        return $approachOfGettingColumnContent;
    }
}
