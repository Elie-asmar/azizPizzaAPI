<?php

use App\Http\Controllers\category;
use App\Http\Controllers\clients;
use App\Http\Controllers\clientuser;
use App\Http\Controllers\group;
use App\Http\Controllers\items;
use App\Http\Controllers\login;
use App\Http\Middleware\Authentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::post('clients/upsertClient', [clients::class, 'upsert'])->middleware(Authentication::class);
// Route::post('clients/upsertClientUser', [clientuser::class, 'upsert'])->middleware(Authentication::class);
// Route::get('clients/get', [clients::class, 'get']);
// Route::get('clients/cloneClient', [clients::class, 'clone']);
// Route::get('clients/removeClient', [clients::class, 'delete']);

Route::post('/login', [login::class, 'login']);



// Route::post('/category/upsert', [category::class, 'upsert'])->middleware(Authentication::class);
// Route::post('/category/swap', [category::class, 'swap'])->middleware(Authentication::class);
// Route::get('/category/get', [category::class, 'get']);
// Route::post('/category/delete', [category::class, 'delete'])->middleware(Authentication::class);

// Route::post('/group/upsert', [group::class, 'upsert'])->middleware(Authentication::class);
// Route::post('/group/swap', [group::class, 'swap'])->middleware(Authentication::class);
// Route::get('/group/get', [group::class, 'get']);
// Route::get('/group/getbyclient', [group::class, 'getByClient']);
// Route::post('/group/delete', [group::class, 'delete'])->middleware(Authentication::class);


// Route::post('item/upsert', [items::class, 'upsert'])->middleware(Authentication::class);
// Route::post('item/delete', [items::class, 'delete'])->middleware(Authentication::class);
// Route::post('item/swap', [items::class, 'swap'])->middleware(Authentication::class);
