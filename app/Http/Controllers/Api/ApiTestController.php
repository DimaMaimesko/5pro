<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;

class ApiTestController extends Controller
{

    public function getUser($username)
    {
        $client = new GuzzleClient();
        $response = $client->get("https://api.github.com/users/$username");
        $result = json_decode($response->getBody()->getContents());
        dd($result);
    }
}
