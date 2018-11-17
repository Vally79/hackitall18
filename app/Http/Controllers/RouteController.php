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
            'time' => $decoded->routes[0]->summary->duration / 360,
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

    public function distance($lat1, $lon1, $lat2, $lon2) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return ($miles * 1.609344);
    }

    public function computeRoute($start, $finish, $max_time)
    {
        $result = $this->getRoute($start, $finish);
        $time = $result['time'];

        if ($time > $max_time)
            return "No";

        $raspuns = array();
        $raspuns['time'] = 0;

        $power = $result['distance'] * 0.175;
        if ($power > 100) {
            $min_sum = INF;
            $chosen_station = -1;
            foreach ($this->chargingStations as $station) {
                $a = $this->distance($start['lat'], $start['lon'], $station['lat'], $station['lon']);
                $a += $this->distance($finish['lat'], $finish['lon'], $station['lat'], $station['lon']);
                if ($a < $min_sum) {
                    $min_sum = $a;
                    $chosen_station = $station;
                }
            }
            $prima = $this->computeRoute($start, $chosen_station, $max_time / 2 - 1);
            $doua = $this->computeRoute($chosen_station, $finish, $max_time / 2 - 1);
            if ($prima == "No" || $doua == "No" || $prima['time'] + $doua['time'] > $max_time)
                return "No";
            else {
                $raspuns = array();
                $raspuns['time'] = $prima['time'] + $doua['time'] + 2;
                $raspuns['drum'] = array_merge($prima['drum'], $doua['drum']);
                return $raspuns;
            }
        } else {
            $raspuns['drum'] = $result['coordinates'];
            array_push($raspuns['drum'], $start);
            $atractii = $this->getTouristAttractions($start, $finish);
            $directie = 0;
            if (abs($start['lat'] - $finish - ['lat']) < abs($start['lon'] - $finish['lon']))
                $directie = 1;
            $marime = count($atractii);
            $curent = $start;
            $gasite = true;
            while ($gasite) {
                $dist = INF;
                $gasite = false;
                $catelea = 0;
                $cate = count($atractii);
                $candidat = $curent;
                for ($i = 0; $i < $cate; $i++) {
                    if ($directie == 0) {
                        if ($curent['lat'] < $atractii[$i]['lat'] && $atractii[$i]['lat'] < $finish[$i]['lat'] && $this->distance($curent['lat'], $curent['lon'], $atractii[$i]['lat'], $atractii[$i]['lon']) < $dist) {
                            $dist = $this->distance($curent['lat'], $curent['lon'], $atractii[$i]['lat'], $atractii[$i]['lon']);
                            $gasite = true;
                            $candidat = $atractii[$i];
                        }
                    } else {
                        if ($curent['lon'] < $atractii[$i]['lon'] && $atractii[$i]['lon'] < $finish[$i]['lon'] && $this->distance($curent['lat'], $curent['lon'], $atractii[$i]['lat'], $atractii[$i]['lon']) < $dist) {
                            $dist = $this->distance($curent['lat'], $curent['lon'], $atractii[$i]['lat'], $atractii[$i]['lon']);
                            $gasite = true;
                            $candidat = $atractii[$i];
                        }
                    }
                }
                $rezultat = $this->getRoute($candidat, $finish);
                if ($rezultat['time'] + $raspuns['time'] > $max_time) {
                    $gasite = false;
                    $raspuns['time'] += $rezultat['time'];
                }
                array_push($raspuns['drum'], $candidat);
                $curent = $candidat;
            }
            return $raspuns;
        }
    }

    public function main(Request $request)
    {
        $this->chargingStations = $this->getChargingStations();
    }
}