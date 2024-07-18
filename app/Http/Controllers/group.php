<?php

namespace App\Http\Controllers;

use App\Models\tbl_categories;
use App\Models\tbl_clients;
use App\Models\tbl_groups;
use App\Models\tbl_items;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use stdClass;
use Throwable;

class group extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/group/upsert",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"categoryid","groupname"},
     *                 @OA\Property(
     *                     property="categoryid",
     *                     description="Category ID",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="groupid",
     *                     description="Group ID (when provided, item will be updated, otherwise created)",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="groupname",
     *                     description="Group Name",
     *                     type="string"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Successfully Saved"),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="401", description="Invalid Token (Usually Occurs when token is expired, which requires re-login)"),
     *     @OA\Response(response="404", description="Invalid Category ID <br/>Invalid Group ID"),
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

            $cat = tbl_categories::where('cat_id', $req->categoryid)->first();

            if (!$cat) {
                return response('Invalid Category ID', 404);
            }
            if ($req->groupid) {
                $data = tbl_groups::where('grp_id', $req->groupid)->first();
                if (!$data) {
                    return response('Invalid Group ID', 404);
                }
            } else {
                $data = new tbl_groups();
                $order = tbl_groups::where('grp_catid', '=', $req->categoryid)->max('grp_order');
                $order = $order ?? 0;
                $data->grp_order = $order + 1;
                $data->grp_catid = $req->categoryid;
            }
            $data->grp_name = $req->groupname;
            $data->grp_timestamp = $now->format('Y-m-d H:i:s');


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
     *     path="/api/group/swap",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"categoryid","grpId1","grpId2"},
     *                 @OA\Property(
     *                     property="categoryid",
     *                     description="Category ID",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="grpId1",
     *                     description="Group ID1 to swap",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="grpId2",
     *                     description="Group ID2 to swap",
     *                     type="string"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Successfully Saved"),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="401", description="Invalid Token (Usually Occurs when token is expired, which requires re-login)"),
     *     @OA\Response(response="404", description="Invalid Category ID <br/>Invalid Group ID"),
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

            $valid = Validator::make($req->all(), ['categoryid' => ['required'], 'grpId1' => ['required'], 'grpId2' => ['required']], $this->globalMessages());
            if ($valid->fails()) {
                // dd($valid);
                return response($valid->messages()->first(), 400);
            }
            $data = null;
            $now = new DateTime();

            $cat = tbl_categories::where('cat_id', $req->categoryid)->first();

            if (!$cat) {
                return response('Invalid Category ID', 404);
            }

            $grp1 = tbl_groups::where('grp_id', $req->grpId1)->first();
            $grp2 = tbl_groups::where('grp_id', $req->grpId2)->first();

            if (!$grp1) {
                return response('Invalid Group ID', 404);
            }
            if (!$grp2) {
                return response('Invalid Group ID', 404);
            }


            $ord = $grp1->grp_order;
            $grp1->grp_order = $grp2->grp_order;
            $grp2->grp_order = $ord;

            $grp1->grp_timestamp = $now->format('Y-m-d H:i:s');
            $grp2->grp_timestamp = $now->format('Y-m-d H:i:s');

            $grp1->save();
            $grp2->save();

            return response('Saved Successfully', 200);

            // return $data == null ? new stdClass() : $data;
            // return response()->json('ok', 200);
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/group/delete",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"groupid","categoryid"},
     *                 @OA\Property(
     *                     property="groupid",
     *                     description="Group ID",
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
     *     @OA\Response(response="404", description="Invalid Category ID <br/>Invalid Group"),
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
            $rules['groupid'] = ['required', 'integer', 'gt:0'];
            unset($rules['groupname']);

            $valid = Validator::make($req->all(), $rules, $this->globalMessages());
            if ($valid->fails()) {
                return response($valid->messages()->first(), 400);
            }
            $data = null;
            $now = new DateTime();


            $cat = tbl_categories::where('cat_id', $req->categoryid)->first();
            if (!$cat) {
                return response('Invalid Category ID', 404);
            }

            $grp = tbl_groups::where('grp_id', $req->groupid)->first();
            if (!$grp) {
                return response('Invalid Group ID', 404);
            }

            $grp->delete();

            return response('Saved Successfully', 200);

            // return $data == null ? new stdClass() : $data;
            // return response()->json('ok', 200);
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/group/get/",
     *     summary="Get Groups",
     *     @OA\Parameter(
     *         name="categoryid",
     *         in="query",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="groupid",
     *         in="query",
     *         description="Group ID",
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
            $valid = Validator::make($req->all(), ['categoryid' => 'required'], $this->globalMessages());
            if ($valid->fails()) {
                return response($valid->messages()->first(), 400);
            }
            $grp = $req->groupid;
            $cat = $req->categoryid;

            $data = tbl_groups::select(['grp_catid', 'grp_id', 'grp_name', 'grp_order'])->where('grp_catid', '=', $cat);
            if ($grp) {
                $data = $data->where('grp_id', $grp);
            }

            $data = $data->orderBy('grp_order', 'asc')->get();
            $coll = collect($data);

            $data = $coll->map(function (string $val) {
                $obj = json_decode($val);
                $obj1 = new stdClass();
                $obj1->Category = strval($obj->grp_catid);
                $obj1->Group = strval($obj->grp_id);
                $obj1->GroupName = $obj->grp_name;
                $obj1->Order = $obj->grp_order;
                $items = tbl_items::select(['itm_id as ID', 'itm_name as ITEMNAME', 'itm_description as ITEMDESCRIPTION', 'itm_price as PRICE', 'itm_grpid as SL_GROUP', 'itm_order as Order', 'itm_status as status'])->where('itm_grpid', '=', $obj->grp_id)->orderBy('itm_grpid', 'asc')->orderBy('itm_order', 'asc')->get();
                $obj1->Items = $items;
                return $obj1;
            });

            return $data;
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }



    /**
     * @OA\Get(
     *     path="/api/group/getbyclient/",
     *     summary="Get Groups by client",
     *     @OA\Parameter(
     *         name="clientcode",
     *         in="query",
     *         description="Client Code",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="groupid",
     *         in="query",
     *         description="Group ID",
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
    function getByClient(Request $req)
    {
        try {
            $valid = Validator::make($req->all(), ['clientcode' => 'required'], $this->globalMessages());
            if ($valid->fails()) {
                return response($valid->messages()->first(), 400);
            }
            $grp = $req->groupid;
            $client = $req->clientcode;

            $data = tbl_groups::select(['grp_catid', 'grp_id', 'grp_name', 'grp_order'])
                ->from('tbl_groups as grp')
                ->join('tbl_categories as cat', 'grp.grp_catid', '=', 'cat.cat_id')
                ->where('cat.cat_client', '=', $client);
            if ($grp) {
                $data = $data->where('grp_id', $grp);
            }

            $data = $data->orderBy('grp_order', 'asc')->get();
            $coll = collect($data);

            $data = $coll->map(function (string $val) {
                $obj = json_decode($val);
                $obj1 = new stdClass();
                $obj1->Category = strval($obj->grp_catid);
                $obj1->Group = strval($obj->grp_id);
                $obj1->GroupName = $obj->grp_name;
                $obj1->Order = $obj->grp_order;
                $items = tbl_items::select(['itm_id as ID', 'itm_name as ITEMNAME', 'itm_description as ITEMDESCRIPTION', 'itm_price as PRICE', 'itm_grpid as SL_GROUP', 'itm_photo as IMAGE', 'itm_order as Order', 'itm_status as status'])->where('itm_grpid', '=', $obj->grp_id)->orderBy('itm_grpid', 'asc')->orderBy('itm_order', 'asc')->get();
                $obj1->Items = $items;
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

            'categoryid' => ['required', 'integer', 'gt:0'],
            'groupid' => ['nullable', 'integer', 'gt:0'],
            'groupname' => ['required', 'max:200'],
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
