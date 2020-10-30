<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\ReportesModel;

use App\Http\Controllers\Controller;

class ReportesController extends Controller
{

    function formReportes(){

      return view('reportes/formReportes');

    }

    function dataInicialFormReportes(){

      $modelo = new ReportesModel();

      $reportes = $modelo->reportesAsociados(session("usuario_id"));

      return [
        "reportes" => $reportes,
        "response" => true
      ];

    }

}
