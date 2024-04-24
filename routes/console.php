<?php

use App\Console\Commands\DeleteCommand;
use App\Console\Commands\ImportCommand;
use App\Jobs\SitemapJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sitemap', function () {
    SitemapJob::dispatch();
})->monthlyOn(3);

Schedule::command(ImportCommand::class)->monthly();
Schedule::command(DeleteCommand::class)->monthlyOn(2);

Schedule::command('queue:prune-batches')->daily();
