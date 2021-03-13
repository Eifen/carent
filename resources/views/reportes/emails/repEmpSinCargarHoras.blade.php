<!DOCTYPE html>
<html lang="en">
<head>

    <style>

        #body{
            padding:20px;
            width:100%;
        }

        #wrapper_logo{
            display:block;
            margin-bottom: 10px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
            width:600px;
        }

        #wrapper_contenido{
            background-color: #E9ECEF;
            border: 1px solid #F6A81C;
            border-radius: 4px;
            color: #2D323C;
            display: block;
            font-size:16px;
            margin-left: auto;
            margin-right: auto;
            padding: 15px;
            text-align: center;
            width: 700px;
        }

        #wrapper_tabla{
          display: block;
          margin-left: auto;
          margin-right: auto;
          margin-top: 5%;
          text-align: center;
          width: 900px;
        }

        #wrapper_tabla table{
          border: 1px solid rgba(0,0,0,0.2);
          border-collapse: collapse;
          border-radius: 4px;
          border-spacing: 0;
          text-align: left;
          width: 100%;
        }

        #wrapper_tabla table thead tr th{
          background-color: #E9ECEF;
          padding: 10px 20px;
          text-align: left;
        }

        #wrapper_tabla table tbody tr{
          border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        #wrapper_tabla table tbody tr td{
          padding: 10px 20px;
          text-align: left;
        }

    </style>

</head>
<body>

  <div id='body'>
    <div id="wrapper_logo">
        <img src="https://carent.crowe.com.ve:16000/images/logo-carent.png" height="100" class="img-fluid logo" alt="">
    </div>
    <div id="wrapper_contenido">
        <p><b>Reporte Generado el {{ $fecha_reporte }}</b></p>
        <p>El siguiente listado muestra a los empleados que no han cargado <b>Horas Cargables</b> en <b>{{ $dias }} días</b>:</p>
    </div>
    <div id="wrapper_tabla">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>División</th>
            <th>Última fecha en la que cargó</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($empleados as $empleado)

            <tr>
              <td>{{ $empleado->nombre }}</td>
              <td>{{ $empleado->division }}</td>
              <td>{{ $empleado->fecha }}</td>
            </tr>

          @endforeach
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>
