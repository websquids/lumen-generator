<?php

namespace Websquids\LumenGenerator\Console;

use Exception;
use Psy\Shell;
use Psy\Configuration;
use Laravel\Lumen\Application;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\VarDumper\Caster\Caster;

class TinkerCommand extends Command
{
    /**
     * Illuminate application methods to include in the presenter.
     *
     * @var array
     */
    protected static $appProperties = [
        'environment',
        'runningUnitTests',
        'version',
        'path',
        'basePath',
        'databasePath',
        'getConfigurationPath',
        'storagePath',
        'getNamespace',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tinker 
                            {include? : Specify an `include` script}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interact with your application';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->getApplication()->setCatchExceptions(false);

        $config = new Configuration();

        $config->getPresenter()->addCasters(
            $this->getCasters()
        );
  
        $shell = new Shell($config);

        $include = $this->argument('include');
        if (file_exists($include)) {
            $shell->setIncludes([$include]);
        }

        $shell->run();
    }

    /**
     * Get an array of Laravel tailored casters.
     *
     * @return array
     */
    protected function getCasters()
    {
        return [
            'Laravel\Lumen\Application' => 'Websquids\LumenGenerator\Console\TinkerCommand::castApplication',
            'Illuminate\Support\Collection' => 'Websquids\LumenGenerator\Console\TinkerCommand::castCollection',
            'Illuminate\Database\Eloquent\Model' => 'Websquids\LumenGenerator\Console\TinkerCommand::castModel',
        ];
    }

    /**
     * Get an array representing the properties of a model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public static function castModel(Model $model)
    {
        $attributes = array_merge(
            $model->getAttributes(),
            $model->getRelations()
        );

        $visible = array_flip(
            $model->getVisible() ?: array_diff(array_keys($attributes), $model->getHidden())
        );

        $results = [];

        foreach (array_intersect_key($attributes, $visible) as $key => $value) {
            $results[(isset($visible[$key]) ? Caster::PREFIX_VIRTUAL : Caster::PREFIX_PROTECTED).$key] = $value;
        }

        return $results;
    }

    /**
     * Get an array representing the properties of an application.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    public static function castApplication(Application $app)
    {
        $results = [];

        foreach (self::$appProperties as $property) {
            try {
                $val = $app->$property();

                $results[Caster::PREFIX_VIRTUAL.lcfirst(preg_replace('/^get/', '', $property))] = $val;
            } catch (Exception $e) {
            }
        }

        return $results;
    }

    /**
     * Get an array representing the properties of a collection.
     *
     * @param \Illuminate\Support\Collection $collection
     *
     * @return array
     */
    public static function castCollection(Collection $collection)
    {
        return [
            Caster::PREFIX_VIRTUAL.'all' => $collection->all(),
        ];
    }
}
