<?php

use Layerok\Restapi\Http\Controllers\CustomerController;
use Layerok\Restapi\Http\Controllers\ResetPasswordController;
use Layerok\Restapi\Http\Controllers\RestorePasswordController;
use Layerok\Restapi\Http\Controllers\SpotController;
use Layerok\Restapi\Http\Controllers\UserController;
use Layerok\Restapi\Http\Middleware\ExceptionsMiddleware;
use \Layerok\Restapi\Http\Controllers\ProductController;
use \Layerok\Restapi\Http\Controllers\CategoryController;
use \Layerok\Restapi\Http\Controllers\CartController;
use \Layerok\Restapi\Http\Middleware\CustomSession;
use \Layerok\Restapi\Http\Controllers\ShippingMethodController;
use \Layerok\Restapi\Http\Controllers\PaymentMethodController;
use \Layerok\Restapi\Http\Controllers\WishlistController;
use \Layerok\Restapi\Http\Controllers\IngredientController;
use \Layerok\Restapi\Http\Controllers\OrderController;
use Layerok\Restapi\Http\Controllers\ActivationController;
use Layerok\Restapi\Http\Controllers\AuthController;
use Layerok\Restapi\Http\Controllers\RefreshController;
use Layerok\Restapi\Http\Controllers\RegistrationController;

Route::group([
    'middleware' => [
        'api',
        ExceptionsMiddleware::class,
        CustomSession::class,
        \Fruitcake\Cors\HandleCors::class,
    ],
    'prefix' => 'api'
], function () {
    Route::get('products', [ProductController::class, 'fetch']);
    Route::get('categories', [CategoryController::class, 'fetch']);
    Route::get('spots', [SpotController::class, 'fetch']);
    Route::get('shipping', [ShippingMethodController::class, 'all']);
    Route::get('payments', [PaymentMethodController::class, 'all']);
    Route::get('ingredients', [IngredientController::class, 'all']);
    Route::prefix('order')->group(function() {
        Route::post('place', [OrderController::class, 'place']);
    });


    Route::prefix('cart')->group(function() {
        Route::get('products', [CartController::class, 'all']);
        Route::get('add', [CartController::class, 'add']);
        Route::get('remove', [CartController::class, 'remove']);
        Route::get('clear', [CartController::class, 'clear']);
    });

    Route::prefix('wishlist')->group(function() {
        Route::get('add', [WishlistController::class, 'add']);
    });

    Route::prefix('auth')->group(function() {
        Route::post('login', AuthController::class);
        Route::post('refresh', RefreshController::class);
        Route::post('register', RegistrationController::class);
        Route::post('activate', ActivationController::class);
        Route::post('reset-password', ResetPasswordController::class);
        Route::post('restore-password', RestorePasswordController::class);
    });

    Route::group([
        'middleware' => [
            \ReaZzon\JWTAuth\Http\Middlewares\ResolveUser::class
        ]
    ], function() {
        Route::get('user', [UserController::class, 'fetch']);
        Route::post('user', [UserController::class, 'save']);
        Route::post('user/password', [UserController::class, 'updatePassword']);
        Route::get('user/addresses', [UserController::class, 'addresses']);
        Route::post('user/address', [UserController::class, 'createAddress']);
        Route::delete('user/address', [UserController::class, 'deleteAddress']);
        Route::post('user/address/default', [UserController::class, 'setDefaultAddress']);

        Route::post('user/customer', [CustomerController::class, 'save']);


    });

});
