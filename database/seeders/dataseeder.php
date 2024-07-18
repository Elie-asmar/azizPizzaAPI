<?php

namespace Database\Seeders;

use App\Models\tbl_categories;
use App\Models\tbl_clients;
use App\Models\tbl_clientusers;
use App\Models\tbl_groups;
use App\Models\tbl_items;
use DateTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class dataseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        tbl_items::truncate();
        tbl_groups::truncate();
        tbl_categories::truncate();
        tbl_clientusers::truncate();
        tbl_clients::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');




        $matchingCats = [];
        $catData = File::get('database/data/categories.json');
        $catData = json_decode($catData);
        $now = new DateTime();

        $client = new tbl_clients();
        $client->clt_code = 'Strand';
        $client->clt_name = 'Strand Resto-Cafe';
        $client->clt_phone = '04295681';
        $client->clt_address = 'Mrouj, Maten, Main Road';
        $client->clt_menutitle = 'Strand-resto Cafe';
        $client->clt_whatsapp = '96171190337';
        $client->clt_serviceexpiry = $now->format('Y-m-d');
        $client->clt_menuurl = 'https://menu.codefolio.site/StrandRestoCafe/#/Menu';
        $client->save();

        $user = new tbl_clientusers();
        $user->usr_client = 'Strand';
        $user->usr_usercode = 'EA';
        $user->usr_username = 'Elie Asmar';
        $user->usr_password = Crypt::encryptString('1234');
        $user->save();



        foreach ($catData as $k => $v) {
            $cat = new tbl_categories();
            $cat->cat_client = $client->clt_code;
            $cat->cat_name = $v->CATEGORYNAME;
            $cat->cat_timestamp = $now->format('Y-m-d H:i:s');
            $order = tbl_categories::where('cat_client', '=', $client->clt_code)->max('cat_order');
            $order = $order ?? 0;
            $cat->cat_order = $order + 1;
            $cat->save();
            $matchingCats[strval($v->CATEGORYID)] = $cat->cat_id;
        }

        $groupData = File::get('database/data/groupitems.json');
        $groupData = json_decode($groupData);

        // var_dump($matchingCats);

        DB::transaction(
            function () use ($groupData, $matchingCats, $now) {
                foreach ($groupData as $k => $v) {
                    $grp = new tbl_groups();
                    $grp->grp_catid = $matchingCats[strval($v->Category)];
                    $grp->grp_name = $v->GroupName;
                    $grp->grp_timestamp = $now->format('Y-m-d H:i:s');
                    $order = tbl_groups::where('grp_catid', '=', $grp->grp_catid)->max('grp_order');
                    $order = $order ?? 0;
                    $grp->grp_order = $order + 1;
                    $grp->save();

                    foreach ($v->Items as $k1 => $v1) {
                        $itm = new tbl_items();
                        $itm->itm_grpid = $grp->grp_id;
                        $itm->itm_name = $v1->ITEMNAME;
                        $itm->itm_description = $v1->ITEMDESCRIPTION;
                        $itm->itm_price = $v1->PRICE ? $v1->PRICE : 0;
                        $itm->itm_timestamp = $now->format('Y-m-d H:i:s');
                        $order = tbl_items::where('itm_grpid', '=', $grp->grp_id)->max('itm_order');
                        $order = $order ?? 0;
                        $itm->itm_order = $order + 1;
                        $itm->itm_status = 'A';
                        $itm->save();
                    }
                }
            }
        );
    }
}
