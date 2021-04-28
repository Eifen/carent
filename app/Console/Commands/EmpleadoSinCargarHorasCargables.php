<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EmpleadoSinCargarHorasCargables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registered:empleadoSinCargarHorasCargables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando que permite saber que empleados no han cargado horas cargables en un tiempo determinado';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

      $controller = app()->make('App\Http\Controllers\Reportes\TotalHorasEmpController');
      app()->call([$controller, 'repEmpSinCargarHoras'], []);
      // EL último [] es para los parametros si se necesita [id => 1, opt => 2]

    }
}
