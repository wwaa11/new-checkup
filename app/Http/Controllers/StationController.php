<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StationController extends Controller
{
    function StationLoginAuth(Request $request)
    {
        $userid = $request->userid;
        $password = $request->password;
        $response = Http::withHeaders([
            'token' => env('API_TOKEN_STAFF')
        ])->post('http://172.20.1.12/dbstaff/api/auth', [
            'userid' => $userid,
            'password' => $password,
        ])->object();
        if($response->status == 1){
            session(['userid' => $response->user->userid , 'name' => $response->user->name]);

            return response()->json(['status' => 1 , 'text' => 'Authentication Success!'],200);
        }

        return response()->json(['status' => 0 , 'text' => 'Authentication Failed!'],200);

    }
    function CheckAuth()
    {

    }
    function test()
    {
        
    }
    function StationLoginPage()
    {

        return view('station.login');
    }
    function StationIndex()
    {

        return view('station.index');
    }
}
