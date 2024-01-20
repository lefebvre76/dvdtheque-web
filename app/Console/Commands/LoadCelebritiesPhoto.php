<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Celebrity;
use App\Services\Tmdb;

class LoadCelebritiesPhoto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dvdtheque:load-celebrities-photo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download photo for celebrities who does not have';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (Celebrity::all() as $celebrity) {
            Tmdb::downloadPersonPhoto($celebrity);
            sleep(1.2);
        };
    }
}
