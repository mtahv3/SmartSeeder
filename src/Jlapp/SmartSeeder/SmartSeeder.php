<?php namespace Jlapp\SmartSeeder;

use Illuminate\Database\Seeder;

class SmartSeeder extends Seeder
{
    /**
     * Get query builder instance for a table.
     *
     * @param  string $table
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table($table)
    {
        return app('db')->table($table);
    }
}
