<?php

namespace Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make\Service\Actions;

trait ServiceDelete
{
    private function getFileContentForServiceDelete(): string
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
            $this->getDynamicRulesForServiceDelete(),
            '        ];' . PHP_EOL,
            '    }' . PHP_EOL . PHP_EOL,
            '    public function handle(): ' . $this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')) . PHP_EOL,
            '    {' . PHP_EOL,
            '        $' . lcfirst($this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace'))) . ' = ' . $this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')) . '::find($this->params[\'id\']);' . PHP_EOL,
            $this->getDynamicParamsSetForHandleMethodForServiceDelete() . PHP_EOL,
            '    }'. PHP_EOL,
            '}'
        ]);
    }

    private function getDynamicRulesForServiceDelete(): string
    {
        $defaultRulesForServiceUpdate = '';

        foreach ($this->getColumns() as $column) {

            if ($column->COLUMN_KEY === 'PRI') {
                $defaultRulesForServiceUpdate .= '            \'' . $column->COLUMN_NAME . '\' => \'';
                $defaultRulesForServiceUpdate .= 'required|' . $this->getRuleByDataType($column) . '\',' . PHP_EOL;
            }
        }

        return $defaultRulesForServiceUpdate;
    }

    private function getDynamicParamsSetForHandleMethodForServiceDelete(): string
    {
        $defaultSettingParamsInHandleMethod = '        $' . lcfirst($this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace')));
        $defaultSettingParamsInHandleMethod .= '->delete();' . PHP_EOL . PHP_EOL;
        $defaultSettingParamsInHandleMethod .= '        return $' . lcfirst($this->getClassNameFromFileWithNamespace($this->argument('file_model_with_namespace'))) . ';';

        return $defaultSettingParamsInHandleMethod;
    }
}
