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
            margin-left: auto;
            margin-right: auto;
            text-align:center;
            width:600px;
        }

        #wrapper_logo .logo-text{
          font-size: 25px;
          font-weight: bold;
          margin-bottom: 10px;
          text-align: center;
        }

        #wrapper_contenido{
            background-color:#E9ECEF;
            border: 1px solid;
            border-bottom-color:#CC1C1C;
            border-left-color: #5080AF;
            border-right-color: #5080AF;
            border-top-color: #FFBE25;
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

    </style>

</head>
<body>

  <div id='body'>
    <div id="wrapper_logo">
        <img src="https://sofguar.com.ve/images/logo.png" height="100" class="img-fluid logo" alt="">
        <div class="logo-text">Crowe</div>
    </div>
    <div id="wrapper_contenido">
        <p><span class="titulo">Contraseña:</span> {{ $clave }}</p>
    </div>
  </div>

</body>
</html>
