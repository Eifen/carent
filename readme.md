<p align="center"><img src="https://carent.crowe.com.ve:16000/images/logo-carent.png" width="400"></p>

## Sobre CARENT

Carent es un sistema web realizado para la firma de auditoria CROWE el cual se encargará de gestionar la Rentabilidad de dicha firma

## ¿Cómo Instalar CARENT?

CARENT es un sistema web desarrollado bajo el framework Laravel y VueJS. Para colocarlo en funcionamiento debes descargartelo o clonartelo y hacer lo siguiente:

- [x] Instalamos el Laravel con el siguiente comando:
```
composer install
```
- [x] Luego instalamos las librerias JavaScript necesarias con el siguiente comando:
```
npm i
```
- [x] Luego instalamos la base de datos que son 2; la primera llamada CARENT y la otra llamada LOGS; estas base de datos estan el directorio llamado **basededatos**.
```
carent/basededatos/carent.sql
carent/basededatos/logs.sql
```
- [x] Por último debes verificar que el archivo **.env** este en tu directorio raíz del proyecto, alli debes colocar la ruta de la base de datos y otros datos necesarios para ejecutar el proyecto; debes solicitarlo al grupo de programadores para terminarlo de configurar.

## ¿Cómo Ejecutar el CARENT?
El CARENT necesita de dos ventanas o consolas para poder "correr"; la primera donde ejecutara el servicio de php para la aplicación y la segunda para ejecutar las librerias JS del proyecto, para ellos debes ejeuctar los siguientes comandos:

- [x] Para compilar el PHP del proyecto:
```
php artisan serve
```

- [x] Para compilar las librerias de JS:
```
npm run watch
```

🚨🚨🚨 **Nota:** Actualmente la aplicación del CARENT posee tareas programada y para ello debemos tener en cuenta o constatar de que en la carpetería de la aplicación exista <b>app/Console/Commands</b> así como el archivo <b>EmpleadoSinCargarHorasCargables.php</b> dentro de ella.

Para saber si el archivo o el comando ya existe debe de ejcutar el siguiente comando por consola:

```
php artisan list
```
Debe de estar en la sección de <b>registered</b> así:

![Sin título](https://user-images.githubusercontent.com/24720946/110642056-089de400-8189-11eb-97bf-4f11c093c992.jpg)

### Ejecutar tareas programadas de manera general

Para ejecutar las tareas programadas solo debes de ejcutar por consola el siguiente comando:

```
php artisan schedule:run
```

### Ejecutar tareas programadas en el servidor

Escribir por consola el siguiente comando si el linux:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Ejecutar localmente la tarea programada

Por lo general no se agregaría una entrada cron en la máquina de desarrollo local. En su lugar, puede utilizar el comando:

```
php artisan schedule:work 
```

Este comando se ejecutará en primer plano e invocará al programador cada minuto hasta que finalice el comando


### Visualizar las tareas programadas activas

```
php artisan schedule:list
```
## Nomenclatura de programación
### Nomenclatura para base de datos

- [x] <b>Nombrar tablas, columnas u otro objeto de base de datos en ingles:</b> esto para hacerlo más comercial a la hora de poder vender el sistema.

Ejemplo de tabla: 
```
cliente => client
```
Ejemplo de nombre de columnas de la tabla client: 
```
id
name
partner_id
service_id
status_id
```

<ul>
    <li>Use solo letras minúsculas, números y guiones bajos</li>
    <li>Use nombres de columna simples y descriptivos</li>
    <li>Use nombres de tablas simples y descriptivos</li>
    <li>Tener una clave primaria entera</li>
    <li></li>
</ul>
