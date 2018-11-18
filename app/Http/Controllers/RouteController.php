<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class RouteController extends Controller
{
    private $chargingStations;
    private $visited = [];
    private $elapsed_time = 0;

    /**
     * Return a list of coordinates for each charging station in a country.
     *
     * @param string $country
     * @return array|bool
     */
    public function getChargingStations($country = 'romania')
    {
        $client = new Client();
        $url = "https://nominatim.openstreetmap.org/search.php?q=charging+stations+in+$country&amenity=charging_station&format=json&limit=1000";
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
                'lon' => $item->lon,
                'charging' => 1,
                'name' => $item->display_name
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

        try {
            $response = $client->get($url);
        } catch (\Exception $exception) {
            $dist = $this->distance($startPoint['lat'], $startPoint['lon'], $endPoint['lat'], $endPoint['lon']);

            return [
                'distance' => $dist,
                'time' => $dist / 50,
                'coordinates' => [$startPoint, $endPoint]
            ];
        }

        if ($response->getStatusCode() != 200) {
            return false;
        }

        $body = (string)$response->getBody();
        $decoded = json_decode($body);
        $details = [
            'distance' => $decoded->routes[0]->summary->distance,
            'time' => $decoded->routes[0]->summary->duration / 3600,
        ];
        $details['coordinates'] = [];

        foreach ($decoded->routes[0]->geometry->coordinates as $coordinate) {
            $details['coordinates'][] = [
                'lat' => $coordinate[1],
                'lon' => $coordinate[0]
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
        $url = "https://nominatim.openstreetmap.org/search.php?q=attractions+in+romania&highway=ways" .
            "&viewbox=$leftMargin,$topMargin,$rightMargin,$bottomMargin&format=json&limit=100";
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
                'lon' => $item->lon,
                'name' => $item->display_name
            ];
        }

        return $coordinates;
    }

    public function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return ($miles * 1.609344);
    }

    private function chooseStation($start, $finish, &$visitedStations)
    {
        $min_sum = INF;
        $chosen_station = -1;
        foreach ($this->chargingStations as $key => $station) {
            $dist = $this->distance($start['lat'], $start['lon'], $station['lat'], $station['lon']);
            $dist += $this->distance($finish['lat'], $finish['lon'], $station['lat'], $station['lon']);

            if ($dist < $min_sum && !isset($visitedStations[$key])) {
                $min_sum = $dist;
                $chosen_station = $key;
            }
        }

        if ($chosen_station == -1) {
            return false;
        }

        $visitedStations[$chosen_station] = true;

        return $this->chargingStations[$chosen_station];
    }

    public function computeRoute($start, $finish, $max_time, &$visitedStations = [])
    {
        $result = $this->getRoute($start, $finish);
        $time = $result['time'];
        $consumption = 0.175;
        $avgSpeed = 50;
        $power = $result['distance'] * $consumption;

        if ($time > $max_time) {
            return [];
        }

        if ($power > 100) {

            $chosen_station = $this->chooseStation($start, $finish, $visitedStations);
            // If there are no charging stations left, return the current best route
            if (!$chosen_station) {
                return [];
            }

            // Compute the max distance for left route and right route
            $startDist = $this->distance($start['lat'], $start['lon'], $chosen_station['lat'], $chosen_station['lon']);
            $finishDist = $this->distance($chosen_station['lat'], $chosen_station['lon'], $finish['lat'], $finish['lon']);
            $leftTime = $startDist / ($startDist + $finishDist) * $max_time;
            $rightTime = $max_time - $leftTime;

            // If there was no left route found, return the best route
            $saveStations = $visitedStations;
            $leftRoute = $this->computeRoute($start, $chosen_station, $leftTime, $visitedStations);
            if (!$leftRoute) {
                $visitedStations = $saveStations;
                return [];
            }

            // Same for right route
            $rightRoute = $this->computeRoute($chosen_station, $finish, $rightTime, $visitedStations);
            if (!$rightRoute) {
                $visitedStations = $saveStations;
                return [];
            }

            // Otherwise, return the new route as it is the best
            return array_merge($leftRoute, $rightRoute);
        } else {
            $bestRoute = [array_merge($start, ['power_left' => 100, 'elapsed_time' => $this->elapsed_time])];
            $totalPower = 80;
            $totalTime = $max_time;
            $attractions = $this->getTouristAttractions($start, $finish);
            $visited = [];

            // While there are still attractions to visit and we still have enough power to get from the last
            // attraction to the destination
            do {
                $lastNode = end($bestRoute);
                $minDist = INF;
                $nextNode = -1;
                // Get the closest attraction that has not been visited
                foreach ($attractions as $key => $attraction) {
                    $dist = $this->distance($lastNode['lat'], $lastNode['lon'], $attraction['lat'], $attraction['lon']);
                    if ($dist < $minDist && !isset($visited[$key]) && !isset($this->visited[json_encode($attraction)])) {
                        $minDist = $dist;
                        $nextNode = $key;
                    }
                }

                if ($nextNode == -1) {
                    break;
                }
                // Add the attraction to the list
                $bestRoute[] = array_merge($attractions[$nextNode], [
                    'power_left' => ($totalPower + 20) - $minDist * $consumption,
                    'elapsed_time' => $lastNode['elapsed_time'] + $minDist / $avgSpeed
                ]);

                // Decrease the total power by the power consumed to reach the attraction
                $totalPower -= $minDist * $consumption;
                $totalTime -= $minDist / $avgSpeed;

                // Mark the new attraction as visited
                $visited[$nextNode] = true;

                $dist = $this->distance($lastNode['lat'], $lastNode['lon'], $finish['lat'], $finish['lon']);
                // If we visited all the attractions, we don't have enough power left to reach the destination,
                // or we aren't reaching our destination in time, break
                if (count($visited) == count($attractions) || $totalPower - $dist * $consumption <= 0 ||
                    $totalTime - $dist / $avgSpeed <= 0) {
                    break;
                }

            } while (true);

            $dist = $this->distance(end($bestRoute)['lat'], end($bestRoute)['lon'], $finish['lat'], $finish['lon']);

            $totalPower -= $dist * $consumption;
            $totalTime -= $dist / $avgSpeed;

            // If we stopped because we didn't have enough power remaining to reach the destination, or we didn't
            // have enough time left, remove the last attraction
            if ($totalPower <= 0 || $totalTime < 0) {
                array_pop($bestRoute);
            }

            foreach ($bestRoute as $value) {
                $this->visited[json_encode($value)] = true;
            }

            $bestRoute[] = array_merge($finish, [
                'elapsed_time' => end($bestRoute)['elapsed_time'] + $dist / $avgSpeed,
                'power_left' => end($bestRoute)['power_left'] - $dist * $consumption
            ]);

            $this->elapsed_time = end($bestRoute)['elapsed_time'];

            // If we still have time, choose a new stop to see even more things
            if ($totalTime > 0) {
                $chosen_station = $this->chooseStation($start, $finish, $visitedStations);

                // If there are no charging stations left, return the current best route
                if (!$chosen_station) {
                    return $bestRoute;
                }

                // Compute the max distance for left route and right route
                $startDist = $this->distance($start['lat'], $start['lon'], $chosen_station['lat'], $chosen_station['lon']);
                $finishDist = $this->distance($chosen_station['lat'], $chosen_station['lon'], $finish['lat'], $finish['lon']);

                // Account for the time spent in the charging station
                $max_time -= $startDist * $consumption / 40;

                $startTime = $startDist / ($startDist + $finishDist) * $max_time;
                $finishTime = $max_time - $startTime;

                // If there was no left route found, return the best route
                $saveStations = $visitedStations;
                $startRoute = $this->computeRoute($start, $chosen_station, $startTime, $visitedStations);
                if (!$startRoute) {
                    $visitedStations = $saveStations;
                    return $bestRoute;
                }

                // Same for right route
                $finishRoute = $this->computeRoute($chosen_station, $finish, $finishTime, $visitedStations);
                if (!$finishRoute) {
                    $visitedStations = $saveStations;
                    return $bestRoute;
                }

                unset($finishRoute[0]);

                $newRoute = array_merge($startRoute, $finishRoute);

                if (count($newRoute) - 1 > count($bestRoute)) {
                    $bestRoute = $newRoute;
                }
            }

            // If there is no time left, return the best route
            return $bestRoute;
        }
    }

    public function main(Request $request)
    {
        $this->chargingStations = $this->getChargingStations();
        $start = array();
        $start['lat'] = $request->latS;
        $start['lon'] = $request->lonS;
        $finish = array();
        $finish['lat'] = $request->lat;
        $finish['lon'] = $request->lon;
        $time = $request->duration;

        $route = $this->computeRoute($start, $finish, $time);
        $rezultat = [];
        for($i = 0; $i < count($route) - 1; $i++)
        {
            $a = $route[$i];
            $b = $route[$i + 1];
            sleep(0.01);
            $rezultat = array_merge($rezultat, $this->getRoute($a, $b)['coordinates']);
        }


        return JsonResponse::create([
            'route' => $rezultat,
            'waypoints' => $route
        ]);
    }
}