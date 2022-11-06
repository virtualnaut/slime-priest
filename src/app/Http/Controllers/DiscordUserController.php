<?php

namespace App\Http\Controllers;

use App\Models\DiscordUser;
use Illuminate\Http\Request;

class DiscordUserController extends Controller
{
    public function show(string $userID)
    {
        return DiscordUser::where('discord_id', '=', $userID)->firstOrFail();
    }
}
