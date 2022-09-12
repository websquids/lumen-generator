<?php

namespace Websquids\LumenGenerator\Console;

use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class SeederMakeCommand extends GeneratorCommand {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:custom_seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new custom seeder class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Seeder';

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer) {
        parent::__construct($files);

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle() {
        parent::handle();

        $this->composer->dumpAutoloads();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub() {
        return __DIR__ . '/stubs/seeder.stub';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name) {
        return $this->laravel->basePath('database') . '/seeds/' . $name . '.php';
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name) {
        $replace = [
            'DummyPluralModelVariable' => Str::plural(lcfirst(class_basename($name))),
        ];

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass($name) {
        return $name;
    }
}
