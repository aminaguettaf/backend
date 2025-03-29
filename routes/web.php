<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

use Illuminate\Support\Facades\Request;

Route::get('/server-info', function () {
    $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'UNKNOWN';
    $phpSapiName = php_sapi_name();
    
    return [
        'server_software' => $serverSoftware, // Apache/Nginx/etc.
        'php_sapi' => $phpSapiName,          // fpm-fcgi, apache2handler, etc.
    ];
});