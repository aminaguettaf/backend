<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdminController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function () {
    return response()->json(['message' => 'Connexion réussie avec React !']);
});

// Produits 
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'delete']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// images
Route::post('/upload', [ImageController::class, 'uploadImage']);

// Commandes
Route::get('/orders', [OrderController::class, 'getAllOrders']);
Route::post('/orders/{id}', [OrderController::class, 'updateOrder']);
Route::delete('/orders/{id}', [OrderController::class, 'deleteOrder']);

// Categories
Route::post('/category', [CategoryController::class, 'addCategory']);
Route::get('/category', [CategoryController::class, 'getAllCategories']);
Route::post('/category/{id}', [CategoryController::class, 'updateCategory']);
Route::delete('/category/{id}', [CategoryController::class, 'deleteCategory']);

// admin
Route::post('/admin/create', [AdminController::class, 'createAdmin']);
Route::post('/admin/login', [AdminController::class, 'login']);
Route::get('/admins', [AdminController::class, 'getAdmins']);