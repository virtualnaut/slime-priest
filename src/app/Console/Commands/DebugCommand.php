<?php

namespace App\Console\Commands;

use App\Services\API\HenrikAPIService;
use App\Services\Discord\BotService;
use Illuminate\Console\Command;

class DebugCommand extends Command
{
    protected $signature = 'debug:generic';

    protected $description = 'Command description';

    public function handle()
    {
        // $api = new HenrikAPIService();
        // dd($api->matchHistory($api->puuid('nebojam', 'BING'), 1));

        $bot = new BotService();



        // $x = $api->matchDetails('9367000b-f90e-477a-b93c-1bbd14381b07')->json();


        // dump(array_keys($x['data']));
        // dump(substr(json_encode($x['data']['rounds']), 0, 1000));

        return Command::SUCCESS;
    }
}
