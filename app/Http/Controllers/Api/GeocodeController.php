<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\City;
use App\Models\Coordinate;
use App\Models\Region;
use GuzzleHttp\Client as GuzzleClient;
use Validator;


class GeocodeController extends Controller
{

   //Usage Example for Postman:  POST   https://5pro.im-sto-gram.com/api/geocode/46.96855382989687/32.01543303802873
    public function writeCoordinates($latitude, $longitude)
    {
        $apiKey = env('GOOGLE_API','AIzaSyDL0LWO9p6xdFB8bfBqJIrwV-iC4cpZ2cI');
        $client = new GuzzleClient();
        $response = $client->get("https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$apiKey");
        $contents = json_decode($response->getBody()->getContents());
        if (count($contents->results) > 0){
            $results = $contents->results[0];
            $formattedAddress = $results->formatted_address;
            $addressComponents = $results->address_components;
            list($region, $city) = $this->parseAddressComponents($addressComponents);
            $coordinates = Coordinate::create(['lat' => $latitude, 'lng' => $longitude]);
            $newAddress = new Address;
            $validateAddress = Validator::make(['name' => $formattedAddress], ['name' => 'unique:addresses,name']);
            if ($validateAddress->passes()){
                $newAddress->name = $formattedAddress;
                $newAddress->save();
                $newAddress->coordinates()->save($coordinates);
                $this->manageRegion($newAddress, $region);
                $this->manageCity($newAddress, $city);
                return [
                    'result' => 'saved',
                    ];
            }else{
                $oldAddress = Address::where('name', $formattedAddress)->get()->first();
                $oldAddress->coordinates()->save($coordinates);
                $messages = $validateAddress->messages();
                return ['result' => 'coordinates attached  to old address', $messages->first('name')];
            }
        }else{
            return ['result' => 'address not found'];
        }
    }

    //Usage Example for Postman:  GET   https://5pro.im-sto-gram.com/api/geocode/regions
    public function getRegions()
    {
        $regions = Region::all(['id', 'name'])->toArray();
        return ['regions' => $regions];
    }

    //Usage Example for Postman:  GET https://5pro.im-sto-gram.com/api/geocode/addresses/1
    public function getAddresses($region_id)
    {
        $addresses = Region::where('id', $region_id)->first();
        if (!empty($addresses)){
            $addresses = $addresses->addresses()->get(['name'])->toArray();
            $addressesFormatted = [];
            foreach ($addresses as $address){
                $addressesFormatted[] = $address['name'];
            }
            return ['addresses' => $addressesFormatted];
        }
        return ['addresses' => 'not found'];
    }


    private function parseAddressComponents($addressComponents)
    {
        $region = null;
        $city = null;
        foreach ($addressComponents as $addressComponent) {
            if (in_array('administrative_area_level_1', $addressComponent->types)){
                $region = $addressComponent->long_name;
            }
            if (in_array('locality', $addressComponent->types)){
                $city = $addressComponent->long_name;
            }
        }
        if (is_null($region)){
            $region = "not found";
        }
        if (is_null($city)){
            $city = "not found";
        }
        return [$region, $city];
    }

    private function manageRegion($newAddress, $region)
    {
        $newRegion = new Region;
        $validateRegion = Validator::make(['name' => $region], ['name' => 'unique:regions,name']);
        if ($validateRegion->passes()){
            $newRegion->name = $region;
            $newRegion->save();
            $newAddress->region()->associate($newRegion->id);
            $newRegion->addresses()->attach($newAddress->id);
        }else{
            $oldRegion = Region::where('name', $region)->get()->first();
            $newAddress->region()->associate($oldRegion->id);
            $oldRegion->addresses()->attach($newAddress->id);
        }
        $newAddress->save();
    }

    private function manageCity($newAddress, $city)
    {
        $newCity = new City;
        $validateCity = Validator::make(['name' => $city], ['name' => 'unique:cities,name']);
        if ($validateCity->passes()){
            $newCity->name = $city;
            $newCity->save();
            $newAddress->city()->associate($newCity->id);
            $newCity->addresses()->attach($newAddress->id);
        }else{
            $oldCity = City::where('name', $city)->get()->first();
            $newAddress->city()->associate($oldCity->id);
            $oldCity->addresses()->attach($newAddress->id);
        }
        $newAddress->save();
    }
}
