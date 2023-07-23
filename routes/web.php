<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HoursController;
use App\Http\Controllers\ProjectController;
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
Route::prefix('/')->group(function () {
    //Control Process
    Route::post('/get-info-select', [ConfigController::class, 'getInfoSelect']);
    //Login
    Route::get('/', [LoginController::class, 'index']);
    Route::post('/login', [LoginController::class, 'Login']);
    Route::get('/logout', [LoginController::class, 'Logout'])->name('logout');
    Route::post('/limit-pag', [ConfigController::class, 'LimitPag']);
    //Usuarios
    Route::prefix('/usuarios')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('users');
        Route::post('/allUsers', [UsersController::class, 'GetAllUser']);
        Route::post('/getParamsInit', [UsersController::class, 'GetInitData']); //Parametros iniciales
        Route::put('/deleteUpdateData', [UsersController::class, 'DeleteDataUpdate']); //Elimina la Session['dataUpdate']
        //Create
        Route::prefix('/create')->group(function () {
            Route::get('/', [UsersController::class, 'index'])->name('createUser');
            Route::post('/newUser', [UsersController::class, 'UserControl']);
        });
        //Update
        Route::prefix('/update')->group(function () {
            Route::get('/', [UsersController::class, 'index'])->name('updateUser');
            Route::post('/loadingUser', [UsersController::class, 'UserPerCode']);
            Route::post('/updateUser', [UsersController::class, 'UserControl']);
        });
    });
    //Clientes
    Route::prefix('/clientes')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('clients');
        Route::post('/allClients', [ClientController::class, 'GetAllClients']);
        Route::post('/getParamsInits', [ClientController::class, 'GetInitData']); //Parametros iniciales
        Route::put('/deleteUpdateData', [ClientController::class, 'DeleteClientUpdate']); //Elimina la Session['clientUpdate']
        Route::post('/info-client', [ClientController::class, 'prepareInfoClient']);
        //Create
        Route::prefix('/create')->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('createClient');
            Route::post('/newClient', [ClientController::class, 'ClientControl']);
        });
        //Update
        Route::prefix('/update')->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('updateClient');
            Route::post('/loadingClient', [ClientController::class, 'ClientPerCode']);
            Route::post('/updateClient', [ClientController::class, 'ClientControl']);
        });
    });
    //Proyectos
    Route::prefix('/projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('projects');
        Route::post('/all-projects', [ProjectController::class, 'getAllProjects']);
        Route::post('/get-params-inits', [ProjectController::class, 'getInitData']);
        Route::put('/delete-update-data', [ProjectController::class, 'deleteProjectUpdate']); //Elimina la Session['projectUpdate']
        Route::post('/info-project', [ProjectController::class, 'prepareInfoProject']);
        //Create
        Route::prefix('/create')->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('createProject');
            Route::post('/new-project', [ProjectController::class, 'projectControl']);
        });
        //Update
        Route::prefix('/update')->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('updateProject');
            Route::post('/loading-project', [ProjectController::class, 'projectPerCode']);
            Route::post('/update-project', [ProjectController::class, 'projectControl']);
        });
        //Assign
        Route::prefix('/assign')->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('assign');
            Route::get('/validate', [ProjectController::class, 'index'])->name('validate');
            Route::post('/assign-users', [ProjectController::class, 'usersPerDepartment']);
            Route::post('/assign-projects', [ProjectController::class, 'getAllAssignProject']);
            Route::post('/re-assign-data', [ProjectController::class, 'reAssignUsers']); //Elimina la Session['usersAssign'] luego de reasignar
            Route::post('/update-asign-projects', [ProjectController::class, 'updateAsign']);
        });
        //Register
        Route::prefix('/register-hours')->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('register');
            Route::post('/prepare-register', [ProjectController::class, 'prepareRegisterHours']);
            Route::post('/get-load-hours', [ProjectController::class, 'prepareLoadHoursPerId']);
            Route::post('/add-hour', [HoursController::class, 'prepareAddHour']);
            Route::post('/add-admin-hour', [HoursController::class, 'prepareAddAdminHour']);
            Route::post('/all-admin-hours', [ProjectController::class, 'allAdminHours']); //Devuelve todos las horas administrativas en formato del preview
            Route::post('/control-load-hours', [HoursController::class, 'prepareControlHour']);
            Route::post('/delete-hour', [HoursController::class, 'prepareDeleteHour']);
        });
        //Close Projects
        Route::prefix('/close')->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('closeProjects');
            Route::post('/prepare-info', [ProjectController::class, 'sessionCloseProjects']);
        });
    });
});
