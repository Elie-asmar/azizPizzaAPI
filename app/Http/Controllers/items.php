<?php

namespace App\Http\Controllers;

use App\Models\tbl_categories;
use App\Models\tbl_clients;
use App\Models\tbl_groups;
use App\Models\tbl_items;
use App\Models\tbl_menuitems;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Encoders\AutoEncoder;
use Throwable;
use Intervention\Image\ImageManager;
use stdClass;

class items extends Controller
{

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


            if ($req->itemid) {
                $data = tbl_menuitems::where('menuItem_id', $req->itemid)->first();
                if (!$data) {
                    return response('Invalid Item ID', 404);
                }
            } else {
                $data = new tbl_menuitems();
                $data->menuItem_id = $this->generateRandomId();
            }
            $data->menuItem_name = $req->name;
            $data->menuItem_desc = $req->description;
            $data->menuItem_ingredient = $req->ingredients;
            $data->menuItem_size = $req->size;
            $data->menuItem_price = $req->price;
            $data->menuItem_category = $req->category;

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

                $itemPath = '/' . $data->menuItem_id .  '/images/menuItems/item' . strval($data->menuItem_id) . '/';

                $files = Storage::disk('public')->files($itemPath);
                Storage::disk('public')->delete($files);

                // $imageDiskPath = $itemPath . $imgObj->filename . '.' . $img[0];
                $imageDiskPath = $itemPath . $imgObj->filename . '.jpg';
                Storage::disk('public')->put($imageDiskPath, $encoded);
                $data->menuItem_img = $imageDiskPath;
            } else {
                $itemPath = '/' . $data->menuItem_id .  '/images/menuItems/item' . strval($data->menuItem_id) . '/';
                $files = Storage::disk('public')->files($itemPath);
                Storage::disk('public')->delete($files);
                $data->menuItem_img = null;
            }

            $data->save();


            return response('Saved Successfully', 200);

            // return $data == null ? new stdClass() : $data;
            // return response()->json('ok', 200);
        } catch (Throwable  | Exception $ex) {
            return response('An error has occured.' . $ex->getMessage(), 400);
        }
    }


    function get(Request $req)
    {
        try {

            $itemId = $req->ID;


            $data = tbl_menuitems::select('*');
            if ($itemId) {
                $data = $data->where('menuItem_id', $itemId);
            }

            $data = $data->get();

            $coll = collect($data);

            $data = $coll->map(function (string $val) {
                $obj = json_decode($val);
                $obj1 = new stdClass();
                $obj1->ID = $obj->menuItem_id;
                $obj1->Name = $obj->menuItem_name;
                $obj1->description = $obj->menuItem_desc;
                $obj1->ingredients = $obj->menuItem_ingredient;
                $obj1->size = $obj->menuItem_size;
                $obj1->price = $obj->menuItem_price;
                $obj1->category = $obj->menuItem_category;
                $obj1->image = $obj->menuItem_img;

                return $obj1;
            });

            return $data;
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
            unset($rules['name']);

            $itm = tbl_menuitems::where('menuItem_id', $req->itemid)->first();
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

            'name' => ['required', 'max:100'],
            'description' => ['max:100'],
            'ingredients' => ['max:200'],
            'category' => ['max:100'],
            'size' => ['max:25'],
            'price' => ['max:25'],
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

    private function generateRandomId($length = 25)
    {
        // Ensure the length is even to match the number of bytes
        $bytesLength = ceil($length / 2);

        // Generate random bytes and convert to hexadecimal
        $randomBytes = random_bytes($bytesLength);
        $randomId = bin2hex($randomBytes);

        // Trim the ID to the desired length if necessary
        return substr($randomId, 0, $length);
    }
}
