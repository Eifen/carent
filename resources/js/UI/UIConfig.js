//Espacio de imports
import FontAwesome from '../Components/FontAwesome/FontAwesome.vue';
import Loading from '../Components/Loading.vue';
import ListingCrud from '../Components/ListingCrud.vue';
import Calendar from '../Components/Calendar.vue';
import axios from 'axios';
import { AXIOSINTERVAL, NOTIFYINTERVAL } from '../app';
//Toastify
import { toast } from 'vue3-toastify';

//Variables globales de UI
export const dataUI = { data(){ 
    return {
        isMounted: false, //Desactiva el loading cuando carga el componente
        lengthColumns: 50,
        maxLengthPagination: 0, //Controlan la páginación
        listData: [] //Object que almacena la data de los usuarios a mostrar en la lista
    }} }
export const componentsUI = { components: { FontAwesome, Loading, ListingCrud, Calendar } }
export const methodsUI = {
    methods: {
        /**
         * Metodo que convierte los objetos de la data en formato JSON
         * @param {*} objectToConvert Almacena el formato del objeto a convertir
         */
        proxyToJson(objectToConvert){ if(this.isMounted) return JSON.parse(JSON.stringify(objectToConvert)) },
        /**
         * Metodo que prepara la data a actualizar de una tabla
         * @param {Object} listDTO Obtiene los parametros de la sesion de la ruta objetivo temporal antes de eliminarla
         * @param {String} route Almacena en un string la URL donde se ejecutara el request PUT
         */
        prepareUpdate(listDTO,route){
            this.updateModel = listDTO
            //Una vez asignado, eliminamos la sesion
            axios.put(route)
            .then(request =>{})
            .catch(error => { console.error(error) });
        },
        /**
         * Metodo que redirecciona a la pantalla anterior
         * @param {String} route Almacena la URL objetivo al redireccionar
         */
        redirectView(route){ window.location.href = route},
    }
}
export const watchUI = {
    watch: {
        //Si carga los usuarios desactivamos el login
        listData(){ this.isMounted = true; } //Desactivamos el loading
    }
}

/*** Clase que se encarga de agrupar los métodos para el control de la data del Carent (Users, Clients, Projects)*/
export class CrudUi
{
    /**
     * Metodo que se encarga de calcular el limite de paginas en la información del Crud. Va en el created
     * @param {Object} self Hereda la data() del padre
     */
    static limitPagData(self,tableTarget,lengthPage)
    {
        axios.post('/limit-pag',{"table":tableTarget,"lengthPage":lengthPage})
        .then(request =>
            {
                if(request.status !== 200) throw request.data;
                //Debe existir una variable llamada maxLengthPagination en aquellos componentes que usen este metodo
                setTimeout(() => {self.maxLengthPagination = Math.ceil(request.data);}, AXIOSINTERVAL);
            })
        .catch(error => { console.error(error) })
    }

    /**
     * Metodo que se encarga de traer del modelo toda la información de la tabla seleccionada en función de su ruta. Va en el mounted
     * @param {String} route Almacena la URL que debe hacer request Axios
     * @param {Object} self Hereda el metodo data() del componente padre
     */
    static getTable(route,self){
        //Cargamos toda la data
        axios.post(route)
        .then(request => {

            if(request.status === 200 && !request.data.response) throw request.data.message;
            //Si no se activa la exceptión, asignamos el objeto
            setTimeout(() => {
                self.listData = request.data.message;
            }, AXIOSINTERVAL);
        })
        .catch(error => { console.error(error); })
    }

    /**
     * Metodo que se encarga de crear un espacio Session en memoria para actualizar una tabla. Es parte de un metodo
     * @param {Object} routes Objeto con el siguiente formato { post: "routeRequest", redirect: "routeToRedirect"}
     * @param {Object} params Parametros que almacena el filtro a realizar (Sea por codigo, Id, entre otras)
     * Debe estar contenido en una propiedad JSON de nombre { "codigoSql": valueToFilter }
     */
    static enableEdit(routes, params){
        axios.post(routes.post,params)
        .then(request => {
            //Verificamos que la data de respuesta no este vacia
            if(request.status === 200 && request.data === '') throw request;
            //Redireccionamos
            window.location.href = routes.redirect
        })
        .catch(error => { console.error(error); })
    }

    /**
     * Metodo que se encarga de hacer request POST al servidor para controlar el Create y Update de una tabla
     * @param {Object} routesSelf Object con el siguiente formato { post: "routeToPostAxios", redirect: "routeToRedirect", self: data()}
     * @param {Object} params Object que captura todos los parametros a filtrar en el request
     */
    static controlCrud(routesSelf, params){
        axios.post(routesSelf.post,params)
        .then(request =>
            {
                if(request.status === 200 && !request.data.response) throw request.data.message;
                toast.success(request.data.message, {
                    position: toast.POSITION.TOP_LEFT,
                    autoClose: false
                });

                setTimeout(() => {
                    window.location.href = routesSelf.redirect;
                }, AXIOSINTERVAL + 200);
            })
        .catch(error =>
            {
                toast.error(error, {
                    position: toast.POSITION.TOP_LEFT,
                    autoClose:NOTIFYINTERVAL
                });

                console.error(error)
                routesSelf.self.isClick = false;
            })
    }
}
