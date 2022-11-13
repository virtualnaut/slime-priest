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

    public function index()
    {
        return PersonOfInterest::all();
    }

    public function create(Request $request)
    {

        $person = new PersonOfInterest(
            [
                'name' => $request->get('name'),
                'tag' => $request->get('tag')
            ]
        );

        $existing = PersonOfInterest::where('name', '=', $person->name)->where('tag', '=', $person->tag)->first();

        if ($existing) {
            return response(['message' => 'Person of interest already exists'], 409);
        }

        $person->puuid = $this->valorant->puuid($person->name, $person->tag);
        $person->save();

        return $person;
    }

    public function destroy(string $name, string $tag)
    {
        $person = PersonOfInterest::where('name', '=', $name)->where('tag', '=', $tag)->firstOrFail();
        $person->delete();

        return response('', 204);
    }
}
