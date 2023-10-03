<?php

use App\Http\Controllers\BillingController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HoursController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Reports\ReportsController;
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
    Route::post('/recovery', [LoginController::class, 'recovery']);
    Route::post('/limit-pag', [ConfigController::class, 'LimitPag']);
    Route::middleware('auth')->group(function () {
        //Configuracion de usuario
        Route::prefix('/account')->group(function () {
            Route::get('/change-password', [ConfigController::class, 'index'])->name('changePassword');
            Route::post('/update-password', [ConfigController::class, 'prepareUpdatePassword']);
        });
        Route::middleware('changeP')->group(function () {
            //Usuarios
            Route::middleware('accessU')->group(function () {
                Route::prefix('/usuarios')->group(function () {
                    Route::get('/', [UsersController::class, 'index'])->name('users');
                    Route::post('/allUsers', [UsersController::class, 'GetAllUser']);
                    Route::post('/getParamsInit', [UsersController::class, 'GetInitData']); //Parametros iniciales
                    Route::put('/deleteUpdateData', [UsersController::class, 'DeleteDataUpdate']); //Elimina la Session['dataUpdate']
                    Route::post('/get-access-user', [UsersController::class, 'previewAccessUser']);
                    Route::post('/update-access-user', [UsersController::class, 'updateAccessUser']);
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
            });
            //Clientes
            Route::middleware('accessC')->group(function () {
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
            });
            //Proyectos
            Route::prefix('/projects')->group(function () {
                Route::get('/', [ProjectController::class, 'index'])->name('projects');
                Route::post('/all-projects', [ProjectController::class, 'getAllProjects']);
                Route::post('/info-project', [ProjectController::class, 'prepareInfoProject']);
                Route::put('/delete-update-data', [ProjectController::class, 'deleteProjectUpdate']); //Elimina la Session['projectUpdate']
                Route::middleware('accessP')->group(function () {
                    Route::post('/get-params-inits', [ProjectController::class, 'getInitData']);
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
                });
                //Assign
                Route::prefix('/assign')->group(function () {
                    Route::middleware('accessASP')->group(function () {
                        Route::get('/', [ProjectController::class, 'index'])->name('assign');
                        Route::post('/assign-users', [ProjectController::class, 'usersPerDepartment']);
                        Route::post('/assign-projects', [ProjectController::class, 'getAllAssignProject']);
                        Route::post('/re-assign-data', [ProjectController::class, 'reAssignUsers']); //Elimina la Session['usersAssign'] luego de reasignar
                        Route::post('/update-asign-projects', [ProjectController::class, 'updateAsign']);
                    });
                    Route::get('/validate', [ProjectController::class, 'index'])->name('validate')->middleware('accessAHP');
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
                Route::middleware('accessCP')->group(function () {
                    Route::prefix('/closure')->group(function () {
                        Route::get('/', [ProjectController::class, 'index'])->name('closeProjects');
                        Route::post('/prepare-info', [ProjectController::class, 'sessionCloseProjects']);
                        Route::post('/submit-close', [ProjectController::class, 'submitClose']);
                    });
                });
            });
            //Billings
            Route::middleware('accessB')->group(function () {
                Route::prefix('/billings')->group(function () {
                    Route::get('/', [BillingController::class, 'index'])->name('billing');
                    Route::post('/all-projects', [BillingController::class, 'getProjectBillings']);
                    Route::post('/loading-project', [BillingController::class, 'billingPerProject']);
                    Route::put('/delete-update-data', [BillingController::class, 'deleteBillingInfo']);

                    //Control billing
                    Route::prefix('/control')->group(function () {
                        Route::get('/', [BillingController::class, 'index'])->name('controlBilling');
                        Route::post('get-params', [BillingController::class, 'prepareParams']);
                        Route::post('submit-billing', [BillingController::class, 'prepareSubmit']);
                        Route::post('refresh-billing', [BillingController::class, 'refreshBilling']);
                        Route::post('delete-billing', [BillingController::class, 'deleteBilling']);
                    });
                });
            });
            //Reportes
            Route::middleware('accessR')->group(function () {
                Route::prefix('/reports')->group(function () {
                    Route::get('/', [ReportsController::class, 'index'])->name('reports');
                    Route::post('/list-reports', [ReportsController::class, 'getListReports']);
                    Route::post('/list-closure-projects', [ReportsController::class, 'getClosureReport']);
                    Route::post('/get-hours-estimated', [ReportsController::class, 'getHoursEstimatedMonth']);
                    Route::post('/list-directive-month', [ReportsController::class, "getDirectiveMonthReport"]);
                    Route::post('/list-admin-hours', [ReportsController::class, "getAdminReport"]);
                    Route::post('/admin-hours-report', [ReportsController::class, "adminIntervalReport"]);
                    Route::post('/list-directive-total', [ReportsController::class, "getDirectiveTotal"]);
                    Route::post('/list-logs-projects', [ReportsController::class, "getLogProject"]);
                });
            });
        });
    });
});
