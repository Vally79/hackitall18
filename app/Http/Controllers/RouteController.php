<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class RouteController extends Controller
{
    private $chargingStations;

    /**
     * Return a list of coordinates for each charging station in a country.
     *
     * @param string $country
     * @return array|bool
     */
    public function getChargingStations($country = 'romania')
    {
        $client = new Client();
        $url = "https://nominatim.openstreetmap.org/search.php?q=charging+stations+in+$country&amenity=charging_station&format=json";
        $response = $client->get($url);

        if ($response->getStatusCode() != 200) {
            return false;
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

    /**
     * Return the route distance in km, duration in hours and waypoints from start to end point. Example:
     * $startPoint = ['lat' => 23, 'lon' => 40], $endPoint = ['lat' => 23', 'lon' => 41]
     *
     * @param $startPoint
     * @param $endPoint
     * @return array|bool
     */
    public function getRoute($startPoint, $endPoint)
    {
        $client = new Client();
        $url = "https://api.openrouteservice.org/directions?api_key=" . env('ORS_KEY') .
            "&coordinates=" . $startPoint['lon'] . "," . $startPoint['lat'] . "|" . $endPoint['lon'] . "," . $endPoint['lat'] .
            "&profile=driving-car" .
            "&units=km" .
            "&geometry_format=geojson";
        $response = $client->get($url);

        if ($response->getStatusCode() != 200) {
            return false;
        }

        $body = (string)$response->getBody();
        $decoded = json_decode($body);
        $details = [
            'distance' => $decoded->routes[0]->summary->distance,
            'duration' => $decoded->routes[0]->summary->duration / 360,
        ];
        $details['coordinates'] = [];

        foreach ($decoded->routes[0]->geometry->coordinates as $coordinate) {
            $details['coordinates'][] = [
                'lat' => $coordinate[0],
                'lon' => $coordinate[1]
            ];
        }

        return $details;
    }

    public function getTouristAttractions($startPoint, $endPoint)
    {
        $latKm = 110;
        $lonKm = 80;
        $lonDiff = abs($endPoint['lon'] - $startPoint['lon']) * $lonKm;
        $latDiff = abs($endPoint['lat'] - $startPoint['lat']) * $latKm;

        // If trip is along X axis
        if ($lonDiff > $latDiff) {
            $dim = $lonDiff / $latKm / 2; // Difference in latitude degrees
            $leftMargin = min($startPoint['lon'], $endPoint['lon']);
            $rightMargin = max($startPoint['lon'], $endPoint['lon']);
            $center = ($startPoint['lat'] + $endPoint['lat']) / 2;
            $topMargin = $center + $dim;
            $bottomMargin = $center - $dim;
        } else {
            $dim = $latDiff / $lonKm / 2; // Difference in longitude degrees
            $topMargin = max($startPoint['lat'], $endPoint['lat']);
            $bottomMargin = min($startPoint['lat'], $endPoint['lat']);
            $center = ($startPoint['lon'] + $endPoint['lon']) / 2;
            $leftMargin = $center - $dim;
            $rightMargin = $center + $dim;
        }

        $client = new Client();
        $url = "https://nominatim.openstreetmap.org/search.php?q=attractions+in+romania" .
            "&viewbox=$leftMargin,$topMargin,$rightMargin,$bottomMargin&format=json&limit=100";
        $response = $client->get($url);

        if ($response->getStatusCode() != 200) {
            return false;
        }

        $body = (string) $response->getBody();
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