<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigModel;

class ConfigController extends Controller
{
    public function LimitPag(Request $dataLimit)
    {
        $configInstance = new ConfigModel();
        $tableTarget = $dataLimit -> input('table');
        $lengthPage = $dataLimit -> input('lengthPage');
        $getData = $configInstance -> CountTable( $tableTarget );

        if($getData['response'] && $getData['data'] > 0 && $getData['data'] !== null)
        {
            //Numero por página
            $numberForPage = $getData['data'] / $lengthPage;
            return response($numberForPage,200);
        }

        //En caso de que no cumpla la condición lanza un SQLSTATE
        return response($getData['data'],500);
    }
}
