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
            width: 600px;
        }

        #wrapper_contenido ul {
          list-style: none;
          text-align: left;
        }

        #wrapper_contenido ul li:before {
          content: '✓';
        }

    </style>

</head>
<body>

  <div id='body'>
    <div id="wrapper_logo">
        <img src="http://201.222.0.202:16000/images/logo-carent.png" height="100" class="img-fluid logo" alt="">
    </div>
    <div id="wrapper_contenido">
        <p>Se ha registrado una eliminación HORA CARGABLE con los siguentes datos:</p>
        <ul>
          <li class="list-group-item"> <b>Empleado:</b> {{ $empleado }}</li>
          <li class="list-group-item"> <b>División:</b> {{ $division }}</li>
          <li class="list-group-item"> <b>Proyecto:</b> {{ $proyecto }}</li>
          <li class="list-group-item"> <b>Descripcion:</b> {{ $descripcion }}</li>
          <li class="list-group-item"> <b>Horas Cargadas:</b> {{ $horas_cargadas }}</li>
          <li class="list-group-item"> <b>Fecha de elimiación:</b> {{ $fecha }}</li>
        </ul>
    </div>
  </div>

</body>
</html>
