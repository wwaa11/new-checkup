<?php
namespace App\Http\Controllers;

use App\Jobs\ProcessClearTask;
use App\Jobs\ProcessCreateTask;
use DB;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    //
    public function test()
    {
        $hour = (int) date('H');

        if ($hour >= 5 && $hour <= 16) {
            $text = 'Service time : ';
        } else {
            $text = 'Out of service time : ';
        }

        return response()->json('CHECKUP PRARAM9 HOSPITAL : ' . date('Y-m-d H:i:s') . ' ' . $text, 200);
    }
    public function startService()
    {
        $jobs  = DB::table('jobs')->get();
        $datas = [];
        foreach ($jobs as $data) {
            $info = json_decode($data->payload);
            $name = explode('\\', $info->displayName);
            switch ($name[2]) {
                case 'ProcessClearTask':
                    $type = 1;
                    break;
                case 'ProcessCreateTask':
                    $type = 2;
                    break;
                case 'ProcessCreateTaskXray':
                    $type = 2;
                    break;
                default:
                    $type = null;
                    break;
            }
            $datas[] = [
                'id'        => $data->id,
                'type'      => $type,
                'name'      => $name[2],
                'create'    => date('d-m-Y H:i:s', $data->created_at),
                'available' => date('d-m-Y H:i:s', $data->available_at),
            ];
        }

        return view('services')->with(compact('datas'));
    }
    public function dispatchCreate()
    {
        ProcessCreateTask::dispatch();
        // ProcessCreateTaskXray::dispatch();

        return response()->json('success', 200);
    }
    public function dispatchClear()
    {
        ProcessClearTask::dispatch()->onQueue('clearing');

        return response()->json('success', 200);
    }
    public function dispatchDelete(Request $request)
    {
        $jobs = DB::table('jobs')->where('id', $request->id)->delete();

        return response()->json('success', 200);
    }

    public function LineMessageCheck()
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.line.me/v2/bot/message/push',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => '{
            "to": "U3d7ba4f0386437906a68612c1cce5eba",
            "messages":[
                {
                    "type":"text",
                    "text":"Services Error",
                    "quickReply": {
                        "items": [
                        {
                            "type": "action",
                            "action": {
                                "type": "uri",
                                "label": "ServicesPages!!!",
                                "uri": "https://pr9webhub.praram9.com/checkup/serviceStart"
                            }
                        }
                        ]
                    }
                }
            ]
        }',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . env('LINE_Token') . '', 'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        return response()->json('success', 200);
    }
}
