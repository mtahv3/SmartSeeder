<?php namespace Jlapp\SmartSeeder;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Database\ConnectionResolverInterface as Resolver;

class SeedMigrator extends Migrator
{
    use AppNamespaceDetectorTrait;

    /**
     * Create a new migrator instance.
     *
     * @param \Jlapp\SmartSeeder\SmartSeederRepository         $repository
     * @param \Illuminate\Database\ConnectionResolverInterface $resolver
     * @param \Illuminate\Filesystem\Filesystem                $files
     */
    public function __construct(
        SmartSeederRepository $repository,
        Resolver $resolver,
        Filesystem $files
    ) {
        parent::__construct($repository, $resolver, $files);
    }

    /**
     * Set environment to run under.
     *
     * @param string $env
     */
    public function setEnv($env)
    {
        $this->repository->setEnv($env);
    }

    /**
     * Get environment we run against.
     *
     * @return string
     */
    public function getEnv()
    {
        return $this->repository->getEnv();
    }

    /**
     * Get all of the migration files in a given path.
     *
     * @param string $path
     *
     * @return array
     */
    public function getMigrationFiles($path)
    {
        // make sure we don't try to merge FALSE that could return from glob
        $envFiles  = $this->repository->getEnv()
                    && $this->files->glob($path.'/'.$this->repository->getEnv().'/*.php') ?: [];

        $globFiles = $this->files->glob($path.'/*.php') ?: [];

        $files = array_merge($envFiles, $globFiles);

        // Once we have the array of files in the directory we will just remove the
        // extension and take the basename of the file which is all we need when
        // finding the migrations that haven't been run against the databases.

        $files = array_map(function ($file) {
            return str_replace('.php', '', basename($file));

        }, $files);

        // Once we have all of the formatted file names we will sort them and since
        // they all start with a timestamp this should give us the migrations in
        // the order they were actually created by the application developers.
        sort($files);

        return $files;
    }

    /**
     * Run the outstanding migrations at a given path.
     *
     * @param string $path
     * @param bool   $pretend
     */
    public function runSingleFile($path, $pretend = false)
    {
        $this->notes = array();

        $file = str_replace('.php', '', basename($path));

        $files = [$file];

        // Once we grab all of the migration files for the path, we will compare them
        // against the migrations that have already been run for this package then
        // run each of the outstanding migrations against a database connection.
        $ran = $this->repository->getRan();

        $migrations = array_diff($files, $ran);

        $filename_ext = pathinfo($path, PATHINFO_EXTENSION);

        if (!$filename_ext) {
            $path .= '.php';
        }
        $this->files->requireOnce($path);

        $this->runMigrationList($migrations, $pretend);
    }

    /**
     * Run "up" a migration instance.
     *
     * @param string $file
     * @param int    $batch
     * @param bool   $pretend
     */
    protected function runUp($file, $batch, $pretend)
    {
        // First we will resolve a "real" instance of the migration class from this
        // migration file name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
        $fullPath = $this->getAppNamespace().$file;
        $seed = new $fullPath();

        if ($pretend) {
            return $this->pretendToRun($seed, 'run');
        }

        $seed->run();

        // Once we have run a migrations class, we will log that it was run in this
        // repository so that we don't try to run it next time we do a migration
        // in the application. A migration repository keeps the migrate order.
        $this->repository->log($file, $batch);

        $this->note("<info>Seeded:</info> $file");
    }

    /**
     * Run "down" a migration instance.
     *
     * @param object $seed
     * @param bool   $pretend
     */
    protected function runDown($seed, $pretend)
    {
        $file = $seed->seed;

        // First we will get the file name of the migration so we can resolve out an
        // instance of the migration. Once we get an instance we can either run a
        // pretend execution of the migration or we can run the real migration.
        $instance = $this->resolve($file);

        if ($pretend) {
            return $this->pretendToRun($instance, 'down');
        }

        if (method_exists($instance, 'down')) {
            $instance->down();
        }

        // Once we have successfully run the migration "down" we will remove it from
        // the migration repository so it will be considered to have not been run
        // by the application then will be able to fire by any later operation.
        $this->repository->delete($seed);

        $this->note("<info>Rolled back:</info> $file");
    }

    /**
     * Resolve a migration instance from a file.
     *
     * @param string $file
     *
     * @return object
     */
    public function resolve($file)
    {
        $filePath = database_path(config('smart-seeder.seedDir').'/'.$file.'.php');
        if ($this->files->exists($filePath)) {
            require_once $filePath;
        } else {
            require_once database_path(
                config('smart-seeder.seedDir').'/'.$this->repository->getEnv().'/'.$file.'.php'
            );
        }

        $fullPath = $this->getAppNamespace().$file;

        return new $fullPath();
    }
}
