@extends('render-layout')

@section('styles')
    <link rel='stylesheet' href={{ asset('css/post-match.css') }} />
@endsection

@section('content')
    <div @class([
        'header',
        'win' => $match->was_win === true,
        'loss' => $match->was_win === false,
    ])>
        <p class='map-name'>{{ $match->map }}</p>
        <div class='score'>
            <span></span>
            <div class='score-chip'>
                <p class='our-score'>{{ $match->our_score }}</p>
                <p>‧</p>
                <p class='their-score'>{{ $match->their_score }}</p>
            </div>
            <span></span>
        </div>
        <p class='mode-name'>{{ $match->mode }}</p>
    </div>
    <div class='main'>
        @foreach ($match->players as $player)
            <div class='player-chip'>
                <img src={{ $player->agent_icon }} />
                <div class='player-stats'>
                    <div class='player-topline'>
                        <p class='player-name'>{{ $player->name }}</p>
                        <div class='player-kd'>
                            <p class='player-kills'>{{ $player->kills }}</p>

                            <span class='bar-container'>
                                <span class='kills-bar'
                                    style={{ 'width:' . ($player->kills / ($player->kills + $player->deaths ?: 1)) * 100 . '%;' }}></span>
                            </span>
                            <p class='player-deaths'>{{ $player->deaths }}</p>
                        </div>
                    </div>
                    <div class='player-bottomline'>
                        <div class='player-stat kd-stat'>
                            <p>K/D</p>
                            <p @class([
                                'good' => $player->kd_ratio >= 1,
                                'bad' => $player->kd_ratio < 1,
                            ])>{{ number_format(round($player->kd_ratio, 2), 2) }}</p>
                        </div>
                        <div class='player-stat'>
                            <p>ACS</p>
                            <p>{{ (int) round($player->average_combat_score) }}</p>
                        </div>
                        <div class='player-stat'>
                            <p>HS%</p>
                            <p>{{ (int) round($player->headshot_percentage) }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div @class([
        'footer',
        'win' => $match->was_win === true,
        'loss' => $match->was_win === false,
    ])>
        <p class='start-time'>{{ $match->start }} &#8729; {{ ((int) round($match->duration / 1000 / 60)) . ' mins' }}</p>
        <p class='server-location'>{{ $match->server }}</p>
    </div>
@endsection
