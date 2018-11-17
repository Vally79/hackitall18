<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class RouteController extends Controller
{
    public function getChargingStations($country = 'romania')
    {
        $client = new Client();
        $url = "https://nominatim.openstreetmap.org/search.php?q=charging+station+in+$country&amenity=charging_station&format=json";
        $res = $client->get($url);
        dd($res->getBody());
    }
}