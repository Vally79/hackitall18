<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class RouteController extends Controller
{
    private $chargingStations;

    public function getChargingStations($country = 'romania')
    {
        $client = new Client();
        $url = "https://nominatim.openstreetmap.org/search.php?q=charging+stations+in+$country&amenity=charging_station&format=json";
        $response = $client->get($url);

        if ($response->getStatusCode() != 200) {
            return JsonResponse::create([
                'status' => 'error',
                'message' => 'External API is not responding'
            ], 500);
        }

        $body = (string)$response->getBody();
        $items = json_decode($body);
        $coordinates = [];
        foreach ($items as $item) {
            $coordinates[] = [
                'lat' => $item->lat,
                'lon' => $item->lon
            ];
        }

        return $coordinates;
    }

    public function main(Request $request)
    {
        $this->chargingStations = $this->getChargingStations();
    }
}