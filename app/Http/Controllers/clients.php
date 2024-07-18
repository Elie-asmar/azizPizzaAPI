<?php

namespace App\Http\Controllers;

use App\Models\tbl_categories;
use App\Models\tbl_clients;
use App\Models\tbl_clientusers;
use App\Models\tbl_groups;
use App\Models\tbl_items;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use stdClass;
use Throwable;

class clients extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/clients/upsertClient",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"code"},
     *                 @OA\Property(
     *                     property="code",
     *                     description="Client Code",
     *                     type="string",
     *
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     description="Client Name",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="phone",
     *                     description="Client Phone",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="menutitle",
     *                     description="Menu Title",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="address",
     *                     description="Address to be displayed om Menu",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="email",
     *                     description="Client Email",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="whatsapp",
     *                     description="Client Whatsapp Ordering Number",
     *                     type="string"
     *                 ),
     *
     *                  @OA\Property(
     *                     property="facebook",
     *                     description="Client Facebook",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="instagram",
     *                     description="Client Facebook",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="validtill",
     *                     description="Valid Till",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="menulogo",
     *                     description="Menu logo ",
     *                     type="object"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Add/update client"),
     *     @OA\Response(response="400", description="Bad Request")
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

            $data = tbl_clients::where('clt_code', '=', DB::raw("'$req->code'"))->first();

            if (!$data) {
                $data = new tbl_clients();
            }

            // var_dump($req->keys());
            $data->clt_code = $req->code;
            if (in_array('name', $req->keys()))
                $data->clt_name = $req->name;
            if (in_array('phone', $req->keys()))
                $data->clt_phone = $req->phone;
            if (in_array('email', $req->keys()))
                $data->clt_email = $req->email;
            if (in_array('facebook', $req->keys()))
                $data->clt_fb = $req->facebook;
            if (in_array('whatsapp', $req->keys()))
                $data->clt_whatsapp = $req->whatsapp;
            if (in_array('instagram', $req->keys()))
                $data->clt_insta = $req->instagram;
            if (in_array('menutitle', $req->keys()))
                $data->clt_menutitle = $req->menutitle;
            if (in_array('address', $req->keys()))
                $data->clt_address = $req->address;
            if (in_array('validtill', $req->keys()))
                $data->clt_serviceexpiry = DateTime::createFromFormat('Y-m-d', $req->validtill);




            if (in_array('menulogo', $req->keys())) {

                //Php casts request objects to associative arrays, here we cast them back to object.
                $imgObj = (object)$req->menulogo;
                $clientPath = '/' . $req->code . '/images/menulogo/';
                $files = Storage::disk('public')->files($clientPath);
                Storage::disk('public')->delete($files);

                if (!$imgObj->deleteImage) {
                    $img = $this->getImageData($imgObj->content);

                    $imageFile = base64_decode($img[1], false);
                    $imageDiskPath = $clientPath . $imgObj->filename . '.' . $img[0];
                    Storage::disk('public')->put($imageDiskPath, $imageFile);
                    $data->clt_menulogo = $imageDiskPath;
                } else {
                    $data->clt_menulogo = null;
                }
            }
            $data->save();

            return response('Saved Successfully', 200);

            // return $data == null ? new stdClass() : $data;
            // return response()->json('ok', 200);
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }

    function clone(Request $req)
    {
        try {


            $valid = Validator::make($req->all(), ['newClientCode' => ['required'], 'oldClientCode' => ['required']], $this->globalMessages());
            if ($valid->fails()) {
                // dd($valid);
                return response($valid->messages()->first(), 400);
            }

            $oldClient = tbl_clients::where('clt_code', '=', DB::raw("'$req->oldClientCode'"))->first();
            if (!$oldClient) {
                return response('Invalid Client', 404);
            }

            $newClient = new tbl_clients();
            $newClient->clt_code = $req->newClientCode;
            $newClient->clt_name = $oldClient->clt_name;
            $newClient->clt_phone = $oldClient->clt_phone;
            $newClient->clt_address = $oldClient->clt_address;
            $newClient->clt_menutitle = $oldClient->clt_menutitle;
            $newClient->clt_serviceexpiry = $oldClient->clt_serviceexpiry;

            DB::transaction(
                function () use ($req, $newClient) {
                    $newClient->save();
                    $oldClientUsers = tbl_clientusers::where('usr_client', $req->oldClientCode)->get();

                    foreach ($oldClientUsers as $v) {
                        $cltU = new tbl_clientusers();
                        $cltU->usr_client = $req->newClientCode;
                        $cltU->usr_usercode = $v['usr_usercode'];
                        $cltU->usr_username = $v['usr_username'];
                        $cltU->usr_password = $v['usr_password'];
                        $cltU->save();
                    }



                    $oldCats = tbl_categories::where('cat_client', $req->oldClientCode)->get();

                    foreach ($oldCats as $k => $v) {
                        $cat = new tbl_categories();
                        $cat->cat_client =  $req->newClientCode;
                        $cat->cat_name = $v['cat_name'];
                        $cat->cat_timestamp = $v['cat_timestamp'];
                        $cat->cat_order = $v['cat_order'];
                        $cat->save();

                        $oldGrps = tbl_groups::where('grp_catid', $v['cat_id'])->get();
                        foreach ($oldGrps as $k1 => $v1) {
                            $grp = new tbl_groups();
                            $grp->grp_catid = $cat->cat_id;
                            $grp->grp_name = $v1['grp_name'];
                            $grp->grp_timestamp = $v1['grp_timestamp'];
                            $grp->grp_order = $v1['grp_order'];
                            $grp->save();

                            $oldItems = tbl_items::where('itm_grpid', $v1['grp_id'])->get();
                            foreach ($oldItems as $k2 => $v2) {
                                $itm = new tbl_items();
                                $itm->itm_grpid = $grp->grp_id;
                                $itm->itm_name = $v2['itm_name'];
                                $itm->itm_description = $v2['itm_description'];
                                $itm->itm_price = $v2['itm_price'];
                                $itm->itm_timestamp = $v2['itm_timestamp'];
                                $itm->itm_order = $v2['itm_order'];
                                $itm->itm_status = $v2['itm_status'];
                                $itm->save();
                            }
                        }
                    }
                }
            );



            return response('Saved Successfully', 200);

            // return $data == null ? new stdClass() : $data;
            // return response()->json('ok', 200);
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }

    function delete(Request $req)
    {
        try {
            $valid = Validator::make($req->all(), ['clientCode' => ['required']], $this->globalMessages());
            if ($valid->fails()) {
                // dd($valid);
                return response($valid->messages()->first(), 400);
            }



            $client = tbl_clients::where('clt_code', '=', DB::raw("'$req->clientCode'"))->first();
            if (!$client) {
                return response('Invalid Client', 404);
            }

            DB::transaction(
                function () use ($req, $client) {

                    $clientUsers = tbl_clientusers::where('usr_client', $req->clientCode)->get();

                    foreach ($clientUsers as $v) {
                        $v->delete();
                    }
                    $oldCats = tbl_categories::where('cat_client', $req->clientCode)->get();
                    // dd($oldCats);

                    foreach ($oldCats as  $v) {
                        $oldGrps = tbl_groups::where('grp_catid', $v['cat_id'])->get();
                        foreach ($oldGrps as  $v1) {
                            $oldItems = tbl_items::where('itm_grpid', $v1['itm_grpid'])->get();
                            foreach ($oldItems as $v2) {
                                $v2->delete();
                            }
                            $v1->delete();
                        }
                        $v->delete();
                    }
                    $client->delete();
                }
            );



            return response('Saved Successfully', 200);

            // return $data == null ? new stdClass() : $data;
            // return response()->json('ok', 200);
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/clients/get/",
     *     summary="Get Client Data",
     *     @OA\Parameter(
     *         name="clientcode",
     *         in="query",
     *         description="Client Code",
     *         required=true,
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
                return response($valid->messages()->first(), 400);
            }
            $client = $req->clientcode;

            $data = tbl_clients::select(['clt_name', 'clt_phone', 'clt_address', 'clt_menutitle', 'clt_email', 'clt_fb', 'clt_insta', 'clt_menulogo', 'clt_whatsapp', 'clt_menuurl'])->where('clt_code', '=', $client)->get();


            $coll = collect($data);

            $data = $coll->map(function (string $val) {
                // var_dump($val);
                $obj = json_decode($val);
                $obj1 = new stdClass();
                $obj1->name = $obj->clt_name;
                $obj1->phone =  $obj->clt_phone;
                $obj1->address = $obj->clt_address;
                $obj1->menutitle = $obj->clt_menutitle;
                $obj1->email = $obj->clt_email;
                $obj1->logo = $obj->clt_menulogo;
                $obj1->fb = $obj->clt_fb;
                $obj1->insta = $obj->clt_insta;
                $obj1->whatsapp = $obj->clt_whatsapp;
                $obj1->url = $obj->clt_menuurl;
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
            'code' => ['required',  'string', 'max:25'], //'regex:/^[A-TV-Z]$/'
            'name' => ['max:200'],
            'phone' => ['max:200'],
            'email' => ['max:200'],
            'facebook' => ['max:200'],
            'instagram' => ['max:200'],
            'validtill' => ['regex:/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/'],
        ];
    }
    private function globalMessages()
    { //used to validate or inputs by using attributes placeholders
        return [
            'required' => 'The :attribute field Is Required',
            'max.string' => 'The :attribute field must not be longer than :max characters.',
            'validtill.regex' => 'The :attribute field must have the following format : "yyyy-mm-dd"',
        ];
    }
    private function getImageData($base64Image)
    {
        list($type, $data) = explode(';', $base64Image);
        list(, $type) = explode('/', $type);
        list(, $data) = explode(',', $data);
        return [$type, $data];
    }
}
