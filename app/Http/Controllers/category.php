<?php

namespace App\Http\Controllers;

use App\Models\tbl_categories;
use App\Models\tbl_clients;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use stdClass;
use Throwable;

class category extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/category/upsert",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"clientcode","categoryname"},
     *                 @OA\Property(
     *                     property="clientcode",
     *                     description="Client Code",
     *                     type="string",
     *
     *                 ),
     *                 @OA\Property(
     *                     property="categoryid",
     *                     description="Category ID (when provided, item will be updated, otherwise created)",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="categoryname",
     *                     description="Category Name",
     *                     type="string"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Successfully Saved"),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="401", description="Invalid Token (Usually Occurs when token is expired, which requires re-login)"),
     *     @OA\Response(response="404", description="Invalid Category ID <br/>Invalid Client"),
     *     @OA\Parameter(
     *         name="X-Custom-Header",
     *         in="header",
     *         description="Custom header for additional information",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     * )
     */
    function upsert(Request $req)
    {
        try {

            $valid = Validator::make($req->all(), $this->insertrules(), $this->globalMessages());
            if ($valid->fails()) {
                // dd($valid);
                return response($valid->messages()->first(), 400);
            }
            $data = null;
            $now = new DateTime();

            $client = tbl_clients::where('clt_code', $req->clientcode)->first();
            if (!$client) {
                return response('Invalid Client', 404);
            }
            if ($req->categoryid) {
                $data = tbl_categories::where('cat_id', $req->categoryid)->first();
                if (!$data) {
                    return response('Invalid Category ID', 404);
                }
            } else {
                $data = new tbl_categories();
                $order = tbl_categories::where('cat_client', '=', $req->clientcode)->max('cat_order');
                $order = $order ?? 0;
                $data->cat_order = $order + 1;
                $data->cat_client = $req->clientcode;
            }
            $data->cat_name = $req->categoryname;
            $data->cat_timestamp = $now->format('Y-m-d H:i:s');


            $data->save();

            return response('Saved Successfully', 200);

            // return $data == null ? new stdClass() : $data;
            // return response()->json('ok', 200);
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/category/swap",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"clientcode","catId1","catId2"},
     *                 @OA\Property(
     *                     property="clientcode",
     *                     description="Client Code",
     *                     type="string",
     *
     *                 ),
     *                 @OA\Property(
     *                     property="catId1",
     *                     description="First Category to be swapped",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="catId2",
     *                     description="Second Category to be swapped",
     *                     type="string"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Successfully Saved"),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="401", description="Invalid Token (Usually Occurs when token is expired, which requires re-login)"),
     *     @OA\Response(response="404", description="Invalid Category ID <br/>Invalid Client"),
     *     @OA\Parameter(
     *         name="X-Custom-Header",
     *         in="header",
     *         description="Custom header for additional information",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     * )
     */

    function swap(Request $req)
    {
        try {

            $valid = Validator::make($req->all(), ["clientcode" => ['required'], "catId1" => ['required'], "catId2" => ['required']], $this->globalMessages());
            if ($valid->fails()) {
                // dd($valid);
                return response($valid->messages()->first(), 400);
            }
            $data = null;
            $now = new DateTime();

            $client = tbl_clients::where('clt_code', $req->clientcode)->first();
            if (!$client) {
                return response('Invalid Client', 404);
            }

            $data = tbl_categories::where('cat_id', $req->catId1)->first();
            if (!$data) {
                return response('Invalid Category ID', 404);
            }
            $data1 = tbl_categories::where('cat_id', $req->catId2)->first();
            if (!$data1) {
                return response('Invalid Category ID', 404);
            }
            $ord = $data1->cat_order;
            $data1->cat_order = $data->cat_order;
            $data->cat_order = $ord;


            $data->cat_timestamp = $now->format('Y-m-d H:i:s');
            $data1->cat_timestamp = $now->format('Y-m-d H:i:s');


            $data->save();
            $data1->save();

            return response('Saved Successfully', 200);

            // return $data == null ? new stdClass() : $data;
            // return response()->json('ok', 200);
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/category/delete",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"clientcode","categoryid"},
     *                 @OA\Property(
     *                     property="clientcode",
     *                     description="Client Code",
     *                     type="string",
     *
     *                 ),
     *                 @OA\Property(
     *                     property="categoryid",
     *                     description="Category ID",
     *                     type="string"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Successfully Saved"),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="401", description="Invalid Token (Usually Occurs when token is expired, which requires re-login)"),
     *     @OA\Response(response="404", description="Invalid Category ID <br/>Invalid Client"),
     *     @OA\Parameter(
     *         name="X-Custom-Header",
     *         in="header",
     *         description="Custom header for additional information",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     * )
     */
    function delete(Request $req)
    {
        try {
            $rules = [...$this->insertrules()];
            $rules['categoryid'] = ['required', 'integer', 'gt:0'];
            unset($rules['categoryname']);

            $valid = Validator::make($req->all(), $rules, $this->globalMessages());
            if ($valid->fails()) {
                // dd($valid);
                return response($valid->messages()->first(), 400);
            }
            $data = null;
            $now = new DateTime();

            $client = tbl_clients::where('clt_code', $req->clientcode)->first();
            if (!$client) {
                return response('Invalid Client', 404);
            }

            $data = tbl_categories::where('cat_id', $req->categoryid)->first();
            if (!$data) {
                return response('Invalid Category ID', 404);
            }

            $data->delete();

            return response('Saved Successfully', 200);

            // return $data == null ? new stdClass() : $data;
            // return response()->json('ok', 200);
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }




    /**
     * @OA\Get(
     *     path="/api/category/get/",
     *     summary="Get categories",
     *     @OA\Parameter(
     *         name="clientcode",
     *         in="query",
     *         description="Client Code",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="categoryid",
     *         in="query",
     *         description="Category ID",
     *         required=false,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Response Body",
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *     ),
     *     @OA\Parameter(
     *         name="X-Custom-Header",
     *         in="header",
     *         description="Custom header for additional information",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     * )
     */
    function get(Request $req)
    {
        try {
            $valid = Validator::make($req->all(), ['clientcode' => 'required'], $this->globalMessages());
            if ($valid->fails()) {
                // dd($valid);
                return response($valid->messages()->first(), 400);
            }

            $client = $req->clientcode;
            $cat = $req->categoryid;

            $data = tbl_categories::select(['cat_id', 'cat_name', 'cat_order'])->where('cat_client', '=', $client);
            if ($cat) {
                $data = $data->where('cat_id', $cat);
            }

            $data->orderBy('cat_order', 'asc');

            $data = $data->get();

            $coll = collect($data);

            $data = $coll->map(function (string $val) {
                $obj = json_decode($val);
                $obj1 = new stdClass();
                $obj1->CATEGORYID = $obj->cat_id;
                $obj1->CATEGORYNAME = $obj->cat_name;
                $obj1->Order = $obj->cat_order;
                return $obj1;
            });

            return $data;
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }


    private function insertrules()
    {
        return [
            'clientcode' => ['required',  'string', 'max:25'], //'regex:/^[A-TV-Z]$/'
            'categoryid' => ['nullable', 'integer', 'gt:0'],
            'categoryname' => ['required', 'max:200'],
        ];
    }
    private function globalMessages()
    { //used to validate or inputs by using attributes placeholders
        return [
            'required' => 'The :attribute field Is Required',
            'max.string' => 'The :attribute field must not be longer than :max characters.',
            'categoryid.integer' => ':attribute Value Must Be > 0',
        ];
    }
}
