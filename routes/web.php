<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\URL;

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

//Ruta principal de inicio de sesion
Route::prefix('/')->group(function(){
    //Login
    Route::get('/', [LoginController::class,'index']);
    Route::post('/login',[LoginController::class,'Login']);
    Route::get('/logout',[LoginController::class,'Logout']);
    //Usuarios
    Route::prefix('/usuarios')->group(function(){
        Route::get('/',[LoginController::class,'index'])->name('users');
        Route::post('/limitPag',[ConfigController::class,'LimitPag']);
        Route::post('/allUsers',[UsersController::class,'index']);
        Route::post('/getTypeDocument',[UsersController::class,'GetTypeDocument']);
        Route::post('/getState',[UsersController::class,'GetEstado']);
        Route::post('/getMunicipality',[UsersController::class,'GetMunicipality']);
        Route::post('/getParish',[UsersController::class,'GetParish']);
        Route::post('/getDivision',[UsersController::class,'GetDivision']);
        Route::post('/getCargo',[UsersController::class,'GetCargo']);
        //Create
        Route::prefix('/create')->group(function(){
            Route::get('/',[LoginController::class,'index'])->name('createUser');
            Route::post('/newUser',[UsersController::class,'NewUser']);
        });
        //Update
        Route::prefix('/update')->group(function(){
            Route::get('/',[LoginController::class,'index'])->name('updateUser');
            Route::post('/updateUser',[UsersController::class,'UpdateUser']);
        });
    });
});
