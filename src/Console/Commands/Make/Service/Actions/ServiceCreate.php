<?php

namespace Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make\Service\Actions;

trait ServiceCreate
{
    private function getFileContentForServiceCreate(): string
    {
        return implode('', [
            '<?php' . PHP_EOL . PHP_EOL,
            'namespace ' . $this->getNamespaceOfFile($this->argument('file_service_with_namespace')) . ';' . PHP_EOL . PHP_EOL,
            'use ' . $this->removeFirstBackslash($this->argument('file_model_with_namespace')) . ';' . PHP_EOL,
            'use Mrzlanx532\LaravelBasicComponents\Service\Service;' . PHP_EOL . PHP_EOL,
            'class ' . $this->getClassNameFromFileWithNamespace($this->argument('file_service_with_namespace')) . ' extends Service' . PHP_EOL,
            '{' . PHP_EOL,
            '    public function getRules(): array' . PHP_EOL,
            '    {' . PHP_EOL,
            '        return [' . PHP_EOL,
            $this->getDynamicRulesForServiceCreate(),
            '        ];' . PHP_EOL,
            '    }' . PHP_EOL . PHP_EOL,
            '    public function handle(): ' . $this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')) . PHP_EOL,
            '    {' . PHP_EOL,
            '        $' . lcfirst($this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace'))) . ' = new ' . $this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')) . '();' . PHP_EOL . PHP_EOL,
            $this->getDynamicParamsSetForHandleMethodForServiceCreate() . PHP_EOL,
            '    }'. PHP_EOL,
            '}'
        ]);
    }

    private function getDynamicRulesForServiceCreate(): string
    {
        $defaultRulesForServiceCreate = '';

        foreach ($this->getColumns() as $column) {

            if ($column->COLUMN_KEY === 'PRI') {
                continue;
            }

            if (in_array($column->COLUMN_NAME, ['created_at', 'updated_at']))
            {
                continue;
            }

            $defaultRulesForServiceCreate .= '            \'' . $column->COLUMN_NAME . '\' => \'';
            $nullableOfRequired = $column->IS_NULLABLE === 'YES' ? 'nullable' : 'required';
            $defaultRulesForServiceCreate .= $nullableOfRequired;
            $defaultRulesForServiceCreate .= '|'.$this->getRuleByDataType($column) . '\',' . PHP_EOL;
        }

        return $defaultRulesForServiceCreate;
    }

    private function getDynamicParamsSetForHandleMethodForServiceCreate(): string
    {
        $defaultSettingParamsInHandleMethod = '';

        foreach ($this->getColumns() as $column) {

            if ($column->COLUMN_KEY === 'PRI') {
                continue;
            }

            if (in_array($column->COLUMN_NAME, ['created_at', 'updated_at']))
            {
                continue;
            }

            $defaultSettingParamsInHandleMethod .= '        ';
            $defaultSettingParamsInHandleMethod .= '$' . lcfirst($this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')));
            $defaultSettingParamsInHandleMethod .= '->' . $column->COLUMN_NAME;

            $defaultSettingParamsInHandleMethod .= ' = $this->params[\'' . $column->COLUMN_NAME . '\']';

            if ($column->IS_NULLABLE === 'YES') {
                $defaultSettingParamsInHandleMethod .= ' ?? null';
            }

            $defaultSettingParamsInHandleMethod .= ';' .PHP_EOL;
        }

        $defaultSettingParamsInHandleMethod .= PHP_EOL . '        $' . lcfirst($this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')));
        $defaultSettingParamsInHandleMethod .= '->save();' . PHP_EOL . PHP_EOL;
        $defaultSettingParamsInHandleMethod .= '        return $' . lcfirst($this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace'))) . ';';

        return $defaultSettingParamsInHandleMethod;
    }
}
