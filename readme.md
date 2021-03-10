<p align="center"><img src="https://carent.crowe.com.ve:16000/images/logo-carent.png" width="400"></p>

## Sobre CARENT

Carent es un sistema web realizado para la firma de auditoria CROWE el cual se encargará de gestionar la Rentabilidad de dicha firma

## ¿Cómo Instalar CARENT?

CARENT es un sistema web desarrollado bajo el framework Laravel y VueJS. Para colocarlo en funcionamiento debes descargartelo o clonartelo y hacer lo siguiente:

- [x] this is a complete item


Actualmente la aplicación del CARENT posee tareas programada y para ello debemos tener en cuenta o constatar de que en la carpetería de la aplicación exista <b>app/Jobs</b>

### Creando Jobs:

<b style='color:#cc0000'>Nota:</b> este paso se realizará si no existe la carpeta app/Jobs o no existe dentro de dicha carpeta el archivo <b>EmpleadoSinCargarHorasCargables.php</b>

```
php artisan make:job EmpleadoSinCargarHorasCargables
```
