<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('Manage buildings. Track rent. Collect dues.');
})->purpose('Display an inspiring quote');
