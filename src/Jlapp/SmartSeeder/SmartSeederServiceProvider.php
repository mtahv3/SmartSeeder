<?php namespace Jlapp\SmartSeeder;

use Illuminate\Support\ServiceProvider;

class SmartSeederServiceProvider extends ServiceProvider
{
    /**
     * Default table for the seeds.
     *
     * @var string
     */
    protected $table = 'seeds';

    /**
     * Default directory for the seed files.
     *
     * @var string
     */
    protected $dir   = 'smart_seeds';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function boot()
    {
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__.'/../../config/smart-seeder.php' => config_path('smart-seeder.php'),
            ]);

        } else {
            // hacky solution for setting config values in lumen
            config([
                'smart-seeder' => [
                    'seedTable' => env('SMARTSEEDER_TABLE', $this->table),
                    'seedDir'   => env('SMARTSEEDER_DIR', $this->dir),
                ]
            ]);
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        if (function_exists('config_path')) {
            $this->mergeConfigFrom(__DIR__.'/../../config/smart-seeder.php', 'smart-seeder');
        }

        $this->app->singleton('seed.repository', function ($app) {
            return new SmartSeederRepository($app['db'], config('smart-seeder.seedTable'), $app->environment());
        });

        $this->app->singleton('seed.migrator', function ($app) {
            return new SeedMigrator($app['seed.repository'], $app['db'], $app['files']);
        });

        $this->app->bind('command.seed', function ($app) {
            return new SeedOverrideCommand($app['seed.migrator']);
        });

        $this->app->bind('seed.run', function ($app) {
            return new SeedCommand($app['seed.migrator']);
        });

        $this->app->bind('seed.install', function ($app) {
            return new SeedInstallCommand($app['seed.repository']);
        });

        $this->app->bind('seed.make', function ($app) {
            return new SeedMakeCommand($app['files']);
        });

        $this->app->bind('seed.reset', function ($app) {
            return new SeedResetCommand($app['seed.migrator'], $app['files']);
        });

        $this->app->bind('seed.rollback', function ($app) {
            return new SeedRollbackCommand($app['seed.migrator'], $app['files']);
        });

        $this->app->bind('seed.refresh', function () {
            return new SeedRefreshCommand();
        });

        $this->commands([
            'seed.run',
            'seed.install',
            'seed.make',

            // These commands require implementation.
            // Currently they are pretty much useless.
            'seed.reset',
            'seed.rollback',
            'seed.refresh',
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'seed.repository',
            'seed.migrator',
            'command.seed',
            'seed.run',
            'seed.install',
            'seed.make',
            'seed.reset',
            'seed.rollback',
            'seed.refresh',
        ];
    }
}
