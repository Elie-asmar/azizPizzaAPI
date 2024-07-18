<?php

namespace App\Http\Controllers;

use App\Models\tbl_clients;
use App\Models\tbl_clientusers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class clientuser extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/clients/upsertClientUser",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"clientcode","usercode"},
     *                 @OA\Property(
     *                     property="clientcode",
     *                     description="Client Code",
     *                     type="string",
     *
     *                 ),
     *                 @OA\Property(
     *                     property="usercode",
     *                     description="User Code",
     *                     type="string",
     *
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     description="User Name",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="password",
     *                     description="User Password, will be encrypted and saved",
     *                     type="string"
     *                 )
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Add/update client user"),
     *     @OA\Response(response="400", description="Bad Request")
     * )
     */
    function upsert(Request $req)
    {
        try {
            // dd("haha");

            $valid = Validator::make($req->all(), $this->insertrules(), $this->globalMessages());
            if ($valid->fails()) {
                // dd($valid);
                return response($valid->messages()->first(), 400);
            }

            $client = tbl_clients::where('clt_code', '=', DB::raw("'$req->clientcode'"))->first();
            // var_dump($client);
            if (!$client) {
                return response("Client Does Not Exist", 400);
            }

            $data = tbl_clientusers::where([['usr_client', '=', DB::raw("'$req->clientcode'")], ['usr_usercode', '=', DB::raw("'$req->usercode'")]])->first();

            // var_dump($data);

            if (!$data) {
                $data = new tbl_clientusers();
            }

            $data->usr_client = $req->clientcode;
            $data->usr_usercode = $req->usercode;
            $data->usr_username = $req->name;

            $data->usr_password =   Crypt::encryptString($req->password);

            $data->save();

            return response('Saved Successfully', 200);

            // return $data == null ? new stdClass() : $data;
            // return response()->json('ok', 200);
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }

    private function insertrules()
    {
        return [
            'clientcode' => ['required',  'string', 'max:25'], //'regex:/^[A-TV-Z]$/'
            'usercode' => ['required', 'string', 'max:25'],
            'name' => ['required', 'string', 'max:200'],
            'password' => ['required']

        ];
    }
    private function globalMessages()
    { //used to validate or inputs by using attributes placeholders
        return [
            'required' => 'The :attribute field Is Required',
            'max.string' => 'The :attribute field must not be longer than :max characters.',

        ];
    }
}
