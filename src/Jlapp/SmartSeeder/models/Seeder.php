<?php

use Illuminate\Database\Eloquent\Model;

class Seeder extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'seeds';

    protected $fillable = [
        'seed',
        'env',
        'batch'
    ];
}
