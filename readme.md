<p align="center"><img src="https://carent.crowe.com.ve:16000/images/logo-carent.png" width="400"></p>

## Sobre CARENT

Carent es un sistema web realizado para la firma de auditoria CROWE el cual se encargará de gestionar la Rentabilidad de dicha firma

## ¿Cómo Instalar CARENT?

CARENT es un sistema web desarrollado bajo el framework Laravel y VueJS. Para colocarlo en funcionamiento debes descargartelo o clonartelo y hacer lo siguiente:

- [x] this is a complete item


Actualmente la aplicación del CARENT posee tareas programada y para ello debemos tener en cuenta o constatar de que en la carpetería de la aplicación exista <b>app/Console/Commands</b>

#### Creando Commands:

🚨🚨🚨 __**Nota:**__ este paso se realizará si no existe la carpeta <b>app/Console/Commands</b> o no existe dentro de dicha carpeta el archivo <b>EmpleadoSinCargarHorasCargables.php</b>

Para saber si el archivo o el comando ya existe debe de ejcutar el siguiente comando por consola:

```
php artisan list
```
Debe de estar en la sección de <b>registered</b> así:

![Sin título](https://user-images.githubusercontent.com/24720946/110642056-089de400-8189-11eb-97bf-4f11c093c992.jpg)

Si no lo ven pueden crearlo de la siguiente manera:

```
php artisan make:command EmpleadoSinCargarHorasCargables --command=registered:empleadoSinCargarHorasCargables
```
