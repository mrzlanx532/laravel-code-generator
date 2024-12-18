<?php

namespace Mrzlanx532\LaravelCodeGenerator\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class LaravelCodeGeneratorProvider extends ServiceProvider
{
    protected array $commands = [
        \Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make\Model::class,
        \Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make\Resource::class,
        \Mrzlanx532\LaravelCodeGenerator\Console\Commands\Make\Service\Service::class,
    ];

    public function register(){
        if (App::environment('local')) {
            $this->commands($this->commands);
        }
    }
}