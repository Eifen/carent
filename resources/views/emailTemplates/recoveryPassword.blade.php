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
            text-align:center;
            width:600px;
        }

        #wrapper_contenido{
            background-color:#E9ECEF;
            border: 1px solid #F6A81C;
            border-radius:4px;
            color:#2D323C;
            display:block;
            font-size:16px;
            margin-left: auto;
            margin-right: auto;
            padding:15px;
            text-align:center;
            width:600px;
        }

        #wrapper_contenido .clave{
          color:#091F40;
          font-size: 35px;
          font-weight: bold;
          margin-top: 10px;
        }

    </style>

</head>
<body>

  <div id='body'>
    <div id="wrapper_logo">
        <img src="https://github.com/sofguar/carent/blob/master/public/images/logo-carent.png?raw=true" height="100" class="img-fluid logo" alt="">
    </div>
    <div id="wrapper_contenido">
        <p>Se ha solicitado la recuperación de su contraseña, si usted no ha realizado esta acción por favor notificar al administrador del sistema.</p>
        <p><span class="titulo">Su contraseña de acceso al sistema es:</span></p>
        <p class="clave">{{ $clave }}</p>
    </div>
  </div>

</body>
</html>
