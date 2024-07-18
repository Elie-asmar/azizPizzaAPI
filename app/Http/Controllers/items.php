<?php

namespace App\Http\Controllers;

use App\Models\tbl_categories;
use App\Models\tbl_clients;
use App\Models\tbl_groups;
use App\Models\tbl_items;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Encoders\AutoEncoder;
use Throwable;
use Intervention\Image\ImageManager;

class items extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/item/upsert",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"groupid","itemname","itemprice"},
     *                 @OA\Property(
     *                     property="itemid",
     *                     description="Item ID (when provided, item will be updated, otherwise created)",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="groupid",
     *                     description="Group ID",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="itemname",
     *                     description="Item Name",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="itemdescription",
     *                     description="Item Description",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="itemprice",
     *                     description="Item Price",
     *                     type="number",
     *                     format="double",
     *                     minimum=0,
     *                     maximum=999999999999.99
     *                 ),
     *                  @OA\Property(
     *                     property="itemimage",
     *                     description="Item Image",
     *                     type="object",
     *                 ),
     *
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Successfully Saved"),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="401", description="Invalid Token (Usually Occurs when token is expired, which requires re-login)"),
     *     @OA\Response(response="404", description="Invalid Group ID <br/>Invalid Item ID"),
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

            $grp = tbl_groups::where('grp_id', $req->groupid)->first();
            $cat = tbl_categories::where('cat_id', $grp->grp_catid)->first();


            if (!$grp) {
                return response('Invalid Group ID', 404);
            }


            if ($req->itemid) {
                $data = tbl_items::where('itm_id', $req->itemid)->first();
                if (!$data) {
                    return response('Invalid Item ID', 404);
                }
            } else {
                $data = new tbl_items();
                $order = tbl_items::where('itm_grpid', '=', $req->groupid)->max('itm_order');
                $order = $order ?? 0;
                $data->itm_order = $order + 1;
                $data->itm_grpid = $req->groupid;
            }
            $data->itm_name = $req->itemname;
            $data->itm_description = $req->itemdescription;
            $data->itm_price = $req->itemprice;

            if ($req->itemstatus && $req->itemid) {
                $data->itm_status = $req->itemstatus;
            } else if (!$req->itemid) {
                $data->itm_status = 'A';
            }

            $data->itm_timestamp = $now->format('Y-m-d H:i:s');
            $data->save();



            if (in_array('itemimage', $req->keys())) {
                //Php casts request objects to associative arrays, here we cast them back to object.
                $imgObj = (object)$req->itemimage;
                $img = $this->getImageData($imgObj->content);

                $imageFile = base64_decode($img[1], false);
                $size =  strlen($imageFile) / 1024;


                $image = ImageManager::gd()->read($imageFile);
                // encode as the originally read image format but with a certain quality
                // $encoded = $image->encode(new AutoEncoder(quality: 1)); // Intervention\Image\EncodedImage
                $encoded = $image->toJpeg($size > 100 ? 10 : 80);

                $itemPath = '/' . $cat->cat_client .  '/images/menuItems/item' . strval($data->itm_id) . '/';

                $files = Storage::disk('public')->files($itemPath);
                Storage::disk('public')->delete($files);

                // $imageDiskPath = $itemPath . $imgObj->filename . '.' . $img[0];
                $imageDiskPath = $itemPath . $imgObj->filename . '.jpg';
                Storage::disk('public')->put($imageDiskPath, $encoded);
                $data->itm_photo = $imageDiskPath;
            } else {
                $itemPath = '/' . $cat->cat_client .  '/images/menuItems/item' . strval($data->itm_id) . '/';
                $files = Storage::disk('public')->files($itemPath);
                Storage::disk('public')->delete($files);
                $data->itm_photo = null;
            }

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
     *     path="/api/item/swap",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"groupid","itemID1","itemID2"},
     *
     *                 @OA\Property(
     *                     property="groupid",
     *                     description="Group ID",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="itemID1",
     *                     description="Item ID1 to swap",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="itemID2",
     *                     description="Item ID2 to swap",
     *                     type="string"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Successfully Saved"),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="401", description="Invalid Token (Usually Occurs when token is expired, which requires re-login)"),
     *     @OA\Response(response="404", description="Invalid Group ID <br/>Invalid Item ID"),
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

            $valid = Validator::make($req->all(), ["groupid" => ['required'], "itemID1" => ['required', "itemID2" => ['required']]], $this->globalMessages());
            if ($valid->fails()) {
                // dd($valid);
                return response($valid->messages()->first(), 400);
            }
            $data = null;
            $now = new DateTime();

            $grp = tbl_groups::where('grp_id', $req->groupid)->first();
            $cat = tbl_categories::where('cat_id', $grp->grp_catid)->first();


            if (!$grp) {
                return response('Invalid Group ID', 404);
            }

            $data = tbl_items::where('itm_id', $req->itemID1)->first();
            if (!$data) {
                return response('Invalid Item ID', 404);
            }
            $data1 = tbl_items::where('itm_id', $req->itemID2)->first();
            if (!$data1) {
                return response('Invalid Item ID', 404);
            }
            $ord = $data1->itm_order;
            $data1->itm_order = $data->itm_order;
            $data->itm_order = $ord;

            $data->itm_timestamp = $now->format('Y-m-d H:i:s');
            $data1->itm_timestamp = $now->format('Y-m-d H:i:s');


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
     *     path="/api/item/delete",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"groupid","itemid"},
     *                 @OA\Property(
     *                     property="groupid",
     *                     description="Group ID",
     *                     type="string",
     *
     *                 ),
     *                 @OA\Property(
     *                     property="itemid",
     *                     description="Item ID",
     *                     type="string"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Successfully Saved"),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="401", description="Invalid Token (Usually Occurs when token is expired, which requires re-login)"),
     *     @OA\Response(response="404", description="Invalid Group ID <br/>Invalid Item"),
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
            $rules['itemid'] = ['required', 'integer', 'gt:0'];
            unset($rules['itemname']);
            unset($rules['itemprice']);

            $valid = Validator::make($req->all(), $rules, $this->globalMessages());
            if ($valid->fails()) {
                return response($valid->messages()->first(), 400);
            }
            $data = null;
            $now = new DateTime();

            $grp = tbl_groups::where('grp_id', $req->groupid)->first();
            if (!$grp) {
                return response('Invalid Group ID', 404);
            }

            $itm = tbl_items::where('itm_id', $req->itemid)->first();
            if (!$itm) {
                return response('Invalid Item ID', 404);
            }

            $itm->delete();

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
            'groupid' => ['required', 'integer', 'gt:0'],
            'itemid' => ['nullable', 'integer', 'gt:0'],
            'itemname' => ['required', 'max:200'],
            'itemdescription' => ['max:2000'],
            'itemprice' => ['required', 'decimal:0,2', 'gt:0'],
        ];
    }
    private function globalMessages()
    { //used to validate or inputs by using attributes placeholders
        return [
            'required' => 'The :attribute field Is Required',
            'max.string' => 'The :attribute field must not be longer than :max characters.',
            'groupid.integer' => ':attribute Value Must Be > 0',
            'itemid.integer' => ':attribute Value Must Be > 0',
            'itemprice.decimal' => ':attribute Value Must Be > 0',
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
