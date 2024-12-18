<?php

namespace Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make\Service\Actions;

trait ServiceUpdate
{
    private function getFileContentForServiceUpdate(): string
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
            $this->getDynamicRulesForServiceUpdate(),
            '        ];' . PHP_EOL,
            '    }' . PHP_EOL . PHP_EOL,
            '    public function handle(): ' . $this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')) . PHP_EOL,
            '    {' . PHP_EOL,
            '        $' . lcfirst($this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace'))) . ' = ' . $this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')) . '::find($this->params[\'id\']);' . PHP_EOL . PHP_EOL,
            $this->getDynamicParamsSetForHandleMethodForServiceUpdate() . PHP_EOL,
            '    }'. PHP_EOL,
            '}'
        ]);
    }

    private function getDynamicRulesForServiceUpdate(): string
    {
        $defaultRulesForServiceUpdate = '';

        foreach ($this->getColumns() as $column) {

            if (in_array($column->COLUMN_NAME, ['created_at', 'updated_at']))
            {
                continue;
            }

            $defaultRulesForServiceUpdate .= '            \'' . $column->COLUMN_NAME . '\' => \'';

            $nullableOfRequired = $column->IS_NULLABLE === 'YES' ? 'nullable' : '';
            if ($column->COLUMN_KEY === 'PRI') {
                $nullableOfRequired = 'required';
            }

            $defaultRulesForServiceUpdate .= $nullableOfRequired ? $nullableOfRequired.'|' : '';
            $defaultRulesForServiceUpdate .= $this->getRuleByDataType($column) . '\',' . PHP_EOL;
        }

        return $defaultRulesForServiceUpdate;
    }

    private function getDynamicParamsSetForHandleMethodForServiceUpdate(): string
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

            if ($column->IS_NULLABLE === 'YES') {
                $defaultSettingParamsInHandleMethod .= ' = array_key_exists(\'' . $column->COLUMN_NAME . '\', $this->params) ? ';
                $defaultSettingParamsInHandleMethod .= '$this->params[\'' . $column->COLUMN_NAME . '\'] : ';
            } else {
                $defaultSettingParamsInHandleMethod .= ' = $this->params[\'' . $column->COLUMN_NAME . '\'] ?? ';
            }

            $defaultSettingParamsInHandleMethod .= '$' . lcfirst($this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace'))) . '->' . $column->COLUMN_NAME;
            $defaultSettingParamsInHandleMethod .= ';' .PHP_EOL;
        }

        $defaultSettingParamsInHandleMethod .= PHP_EOL . '        $' . lcfirst($this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')));
        $defaultSettingParamsInHandleMethod .= '->save();' . PHP_EOL . PHP_EOL;
        $defaultSettingParamsInHandleMethod .= '        return $' . lcfirst($this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace'))) . ';';

        return $defaultSettingParamsInHandleMethod;
    }
}
