<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Process\Pipe;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessTest;
use App\Models\Station;
use App\Models\Substation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StationController extends Controller
{
    function test()
    {
        // $arr = ['1','2','3','4','5'];
        // ProcessTest::dispatch(array_rand($arr));
        // sleep(seconds: 5);

        // $result = Process::pipe(function (Pipe $pipe) {
        //     Log::channel('daily')->debug('start.');
        //     sleep(1);
        //     Log::channel('daily')->debug('end.');
        //     $pipe->command(dump('success'));
        // });
        // dump($result);

        // return redirect(env('APP_URL').'/');

        return response()->json('index new check up page '.date('Y-m-d H:i:s') , 200);
    }
    function Auth()
    {

        return view('station.login');
    }
    function AuthCheck(Request $request)
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

            $user = User::firstOrCreate([
                'userid' => $response->user->userid,
                'name' => $response->user->name,
            ]);

            if (Auth::loginUsingId($user->id)) {

                return response()->json(['status' => 1 , 'text' => 'Authentication Success!'],200);
            }else{

                return response()->json(['status'=> 0,'text'=> 'Authentication Success , User not found!'],200);
            }
        }

        return response()->json(['status' => 0 , 'text' => 'Authentication Failed!'],200);

    }
    function StationIndex()
    {
        $stations = [];
        $substations = Substation::all();
        foreach ($substations as $subst) {
            $stations[$subst->station->name][] = [
                'id'=> $subst->id,
                'name'=> $subst->name
            ];
        }
        
        return view('station.index', ['stations' => $stations]);
    }
}
