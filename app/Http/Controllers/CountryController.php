<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Symfony\Component\HttpFoundation\Response;

class CountryController extends Controller
{
    public function list()
    {
        $countries = Country::all();
        $data = [];
        foreach ($countries as $c) {
            $data[] = [
                'id' => $c->id,
                'text' =>  $c->name. ' ' . $c->dial_code,
            ];
        }
        return response(["results" => $data], Response::HTTP_OK);
    }
}
