<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ClientController;
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
        Route::get('/',[UsersController::class,'index'])->name('users');
        Route::post('/limitPag',[ConfigController::class,'LimitPag']);
        Route::post('/allUsers',[UsersController::class,'GetAllUser']);
        Route::post('/getParamsInit',[UsersController::class,'GetInitData']); //Parametros iniciales
        Route::post('/getMunicipality',[UsersController::class,'GetMunicipality']);
        Route::post('/getParish',[UsersController::class,'GetParish']);
        //Create
        Route::prefix('/create')->group(function(){
            Route::get('/',[UsersController::class,'index'])->name('createUser');
            Route::post('/newUser',[UsersController::class,'UserControl']);
        });
        //Update
        Route::prefix('/update')->group(function(){
            Route::get('/',[UsersController::class,'index'])->name('updateUser');
            Route::post('/loadingUser',[UsersController::class,'UserPerCode']);
            Route::post('/updateUser',[UsersController::class,'UserControl']);
        });
    });
    //Clientes
    Route::prefix('/clientes')->group(function(){
        Route::get('/',[ClientController::class,'index'])->name('clients');
        Route::post('/limitPag',[ConfigController::class,'LimitPag']);
        Route::post('/allClients',[ClientController::class,'GetAllClients']);
        Route::post('/getParamsInits',[ClientController::class,'GetInitData']); //Parametros iniciales
        //Create
        Route::prefix('/create')->group(function(){
            Route::get('/',[ClientController::class,'index'])->name('createClient');
            Route::post('/newClient',[ClientController::class,'ClientControl']);
        });
    });
});
