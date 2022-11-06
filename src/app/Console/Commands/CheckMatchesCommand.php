<?php

namespace App\Console\Commands;

use App\Services\API\HenrikAPIService;
use App\Services\Discord\BotService;
use App\Services\TrackedPersonService;
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

        if (!TrackedPersonService::active()) {
            // No user is currently being tracked, so abort.
            return Command::FAILURE;
        }

        $lastMatch = $valorant->lastMatch(TrackedPersonService::puuid());

        if (Cache::has('last-match-id')) {
            $oldLastMatch = Cache::get('last-match-id');

            if ($oldLastMatch !== $lastMatch) {
                Cache::set('last-match-id', $lastMatch);

                Log::info('Match `$lastMatch` just ended');
                $bot->sendPostMatchSummary(env('DISCORD_OUTPUT_CHANNEL_ID'), $lastMatch);
            } else {
                Log::info('No match ended');
            }
        } else {
            Cache::set('last-match-id', $lastMatch);
        }

        return Command::SUCCESS;
    }
}
