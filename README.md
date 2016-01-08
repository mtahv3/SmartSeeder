# SmartSeeder for Laravel

**Fork of [jlapp/smart-seeder](https://github.com/slampenny/SmartSeeder) adjusted for Lumen 5.2**


### Installation

- Add custom repository and require the package in `composer.json` for your project:
    ```
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/mtahv3/SmartSeeder"
        }
    ],
    "require": {
        ...
        "mtahv3/smart-seeder": "dev-master"
    },
    ```

- Add `Jlapp\SmartSeeder\SmartSeederServiceProvider` to your providers array in `bootstrap/app.php`

    ```
$app->register(Jlapp\SmartSeeder\SmartSeederServiceProvider::class);
```

---

Seeding as it is currently done in Laravel is intended only for dev builds, but what if you're iteratively creating your database and want to constantly flush it and repopulate it during development? What if you want to seed a production database with different data from what you use in development? What if you want to seed a table you've added to a database that is currently in production with new data?

Features
========

- Allows you to seed databases in different environments with different values.
- Allows you to "version" seeds the same way that Laravel currently handles migrations. Running ```php artisan seed``` will only run seeds that haven't already been run.
- Prompts you if your database is in production.
- Allows you to run multiple seeds of the same model/table
- Overrides Laravel's seeding commands. SmartSeeder will fire when you run
    ```
    php artisan db:seed
    ```
     or
    ```
    php artisan migrate:refresh --seed
    ```
- You can run a single seed file with the --file option.
    php artisan seed:run --file=seed_2015_05_27_030017_UserSeeder

Use
=====
When you install SmartSeeder, various artisan commands are made available to you which use the same methodology you're used to using with Migrations.

<table>
<tr><td>seed:run</td><td>Runs all the seeds in the smartSeeds directory that haven't been run yet.</td></tr>
<tr><td>seed:make</td><td>Makes a new seed class in the environment you specify.</td></tr>
<tr><td>seed:rollback</td><td>Rollback doesn't undo seeding (which would be impossible with an auto-incrementing primary key). It just allows you to re-run the last batch of seeds.</td></tr>
<tr><td>seed:reset</td><td>Resets all the seeds.</td></tr>
<tr><td>seed:refresh</td><td>Resets and re-runs all seeds.</td></tr>
<tr><td>seed:install</td><td>You don't have to use this... it will be run automatically when you call "seed"</td></tr>
</table>

