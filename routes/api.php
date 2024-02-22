<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressBookAPI;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/addressbook', [AddressBookAPI::class, 'getAddressData']);

Route::get('/addressbook/{id}',[AddressBookAPI::class, 'getAddressDetail']);

Route::delete('/addressbook/delete/{id}',[AddressBookAPI::class, 'deleteAddressData']);

Route::get('/type',[AddressBookAPI::class, 'getTypeEnum']);

Route::post('/addressbook/create',[AddressBookAPI::class, 'createAddressData']);

Route::post('/addressbook/update/{id}',[AddressBookAPI::class, 'updateAddressData']);

Route::post('/addressbook/json-file',[AddressBookAPI::class, 'importJSONFile']);
