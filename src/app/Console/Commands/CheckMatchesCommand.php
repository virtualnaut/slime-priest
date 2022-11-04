<?php

namespace App\Console\Commands;

use App\Services\API\HenrikAPIService;
use App\Services\Discord\BotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckMatchesCommand extends Command
{
    protected $signature = 'check:matches';

    protected $description = 'Check the VALORANT API for whether there are any new matches';

    public function handle()
    {
        $bot = new BotService();
        $valorant = new HenrikAPIService();

        if (!Cache::has('tracking-user')) {
            // No user is currently being tracked, so abort.
            return Command::FAILURE;
        }

        [$userName, $userTag] = Cache::get('tracking-user');
        $lastMatch = $valorant->lastMatch($valorant->puuid($userName, $userTag));

        if (Cache::has('last-match-id')) {
            $oldLastMatch = Cache::get('last-match-id');

            if ($oldLastMatch !== $lastMatch) {
                Cache::set('last-match-id', $lastMatch);

                Log::info('Game `$lastMatch` just ended');
                $bot->sendTo(env('DISCORD_OUTPUT_CHANNEL_ID'), "Game `$lastMatch` just ended");
            } else {
                Log::info('No match ended');
            }
        } else {
            Cache::set('last-match-id', $lastMatch);
        }

        return Command::SUCCESS;
    }
}
