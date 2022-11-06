<?php

namespace App\Http\Controllers;

use App\Models\PersonOfInterest;
use App\Services\API\HenrikAPIService;
use Illuminate\Http\Request;

class PeopleOfInterestController extends Controller
{
    public function __construct()
    {
        $this->valorant = new HenrikAPIService();
    }

    public function create(Request $request)
    {
        $person = new PersonOfInterest(
            [
                'name' => $request->get('name'),
                'tag' => $request->get('tag')
            ]
        );

        $person->puuid = $this->valorant->puuid($person->name, $person->tag);
        $person->save();

        return $person;
    }
}
