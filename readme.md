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

🚨 La identación para el código de base de datos, PHP, JavaScript, CSS o cualquier otro lenguaje empleado en el sistema debe ser a 4 espacios.

### Nomenclatura para base de datos

- [x] <b>Nombrar tablas, columnas u otro objeto de base de datos en ingles:</b> esto para hacerlo más comercial a la hora de poder vender el sistema.

Ejemplo de tabla: 
```
clientes => clients
```
Ejemplo de nombre de columnas de la tabla clients: 
```
client_id
business_name
code
partner_id
service_id
status_id
```
Ejemplo de procedimiento almacenado para crear un nuevo cliente: 
```
sp_create_client
```
Ejemplo de los parametros de un procedimiento almacenado: 
```
sp_create_client(p_user_id, p_partner_id, ..., p_response)
```
Ejemplo para crear una vista: 
```
vw_clients
```
Ejemplo para crear una función: 
```
fn_clients
```
Para las claves foráneas se debe indicar la tabla origen y luego la tabla destino seguido del campo destino. Un ejemplo podemos imaginar que se necesita crear una clave foránea de la tabla `clients` a la tabla `users` ya que en la tabla cliente hay un campo llamado `partner_id` donde se indica el id del usuario que es el socio para ese cliente, entonces quedaría algo como el siguiente ejemplo: 
```
fk_client_user_id
```

- [x] <b>Use solo letras minúsculas, números y guiones bajos:</b> no utilice puntos, espacios ni guiones en los nombres de bases de datos, esquemas, tablas o columnas. Los puntos son para identificar objetos, normalmente en el patrón base de `esquema.tabla.columna`.

Las consultas son más difíciles de escribir si usa letras mayúsculas en los nombres de tablas o columnas. Si todo está en minúsculas, nadie tiene que recordar si la tabla de usuarios es `Users` o `users`.

- [x] <b>Escribir los nombre de las tablas en plural:</b> escribir las tablas en singular aumenta la probabilidad de colisionar con una palabra reservada dentro de la base de datos

- [x] <b>Use nombres de tablas simples y descriptivos:</b> si el nombre de la tabla se compone de varias palabras, use guiones bajos para separar las palabras. Es mucho más fácil leer `project_invoices` que `projectinvoices`.

Y siempre que sea posible, utilice una palabra en lugar de dos: `invoices`, esto es un ejemplo pero no esta mal en este caso usar `project_invoices` porque se puede emplear solo una tabla `invoices` para todo tipo de facturas y no solo facturas a proyectos o se puede separar en `project_invoices`, todo depende de la normalización de la base de datos en ese momento.

No agregar prefijos a las tablas. Tener tablas con nombres como `tbl_users`, `tbl_clients`, etc, no vale la pena escribirlo porque la naturaleza propia del objeto es ser una tabla además que solo alargas el nombre de la tabla propiamente dicha. Pasa lo contrario con los otros objecto que si se debe colocar un prefijo para identificarlos mejor como `SP` para procedimientos almacenados o `VW` para las vistas, ya que la razón principal de una base de datos es almacenar datos en tablas y estos objectos son menos comunes y se utilizan para manejar mejor el CRUD en dichas tablas.

- [x] <b>Tener una clave primaria entera:</b> toda tabla tiene que poseer una campo de tipo de dato entero que sea clave primaria; puede ser autoincremental o no.

- [x] <b>No usar como nombre en las llaves primaria solo ID:</b> esto para evitar ambiguedades en las consultas y detectar rápido los errores. Ejemplo:
```
id => client_id
```

- [x] <b>Sea consistente con las claves foráneas:</b> para las claves foráneas se debe indicar la tabla origen y luego la tabla destino seguido del campo destino. Un ejemplo podemos imaginar que se necesita crear una clave foránea de la tabla `clients` a la tabla `users` ya que en la tabla cliente hay un campo llamado `partner_id` donde se indica el id del usuario que es el socio para ese cliente, entonces quedaría algo como el siguiente ejemplo: 
```
fk_client_user_id
```
Puede existir el caso donde por ejemplo la tabla `client` puede poseer dos columnas que apuntan como clave foranea a una misma tabla destino `users`, por ejemplo:
```
partner_id -> para indicar el socio por el cual provino ese cliente
user_id -> para indicar el usuario que creo ese cliente
```
Entonces no vamos a crear 2 llaves foráneas con el mismo nombre `fk_client_user_id`; para diferencialos podemos colocar a una de las llaves foráneas nombrandola por su razon de ser quedando:
```
fk_client_partner_id -> para el campo partner_id
fk_client_user_id -> para el campo user_id
```

- [x] <b>Almacenar fechas y horas como fechas y horas:</b> no almacenar las fechas con el tipo de dato `Datetimes`.

### Nomenclatura para base de datos

- [x] Para nombrar clases, componentes Vue usar `Upper Camel Case`.

- [x] <b>Para nombrar clases, componentes Vue usar `PascalCase`:</b> Las clases y los objetos deben tener nombres de sustantivos o frases nominales como Clients, WikiPage, Users y AddressParser. Evite palabras como Administrador, Procesador, Datos o Información en el nombre de una clase. Un nombre de clase no debe ser un verbo.

Ejemplo
```
Clients
```

- [x] Para nombrar variables, urls y propiedades Vue usar `camelCase`.

Ejemplo
```
clientList
```

- [x] Para nombrar urls, `camelCase`.
