<?php

namespace App\Http\Controllers;

use App\Models\tbl_users;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class login extends Controller
{

    function login(Request $req)
    {
        try {

            sleep(0.5);
            $valid = Validator::make($req->all(), $this->insertrules(), $this->globalMessages());
            if ($valid->fails()) {
                // dd($valid);
                return response($valid->messages()->first(), 400);
            }


            $user = tbl_users::where([['user_id', '=', $req->usercode], ['user_password', '=', $req->password]])->first();


            // var_dump($user);

            if ($user == null) {
                return response('Invalid Login', 401);
            } else {


                $resp = [
                    "code" => $req->usercode,
                    "name" => $user->user_name
                ];
                return response(json_encode($resp), 200);
            }
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }



    private function insertrules()
    {
        return [
            'usercode' => 'required',
            'password' => 'string|required',
        ];
    }

    private function logoutrules()
    {
        return [
            'code' => 'required',
            'token' => 'required'
        ];
    }
    private function globalMessages()
    { //used to validate or inputs by using attributes placeholders
        return [
            'required' => 'The :attribute field Is Required',
        ];
    }
}
