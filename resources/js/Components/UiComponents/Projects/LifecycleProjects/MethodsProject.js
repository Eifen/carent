import { Exceptions } from "@/Excepciones/Excepciones"
export const projectMethods = 
{
    methods: 
    {
        /**
         * Metodo que controla el estado de la lista
         * @param {Object} refTarget Objeto que captura la informacion del input, debe tener las siguientes propiedades
         * @property {String} nameRef Nombre del ref a que se hace referencia
         * @property {String} objectRef Nombre del objeto que almacena las referencias de ese componente
         * @property {Strinf} inputRef Nombre del v-model donde se almacena la informacion del ref
         * @param {*} inputTarget Captura que tipo de campo es para controlarlo con noInput
         * Puede ser cliente, socio, gerente, entre otros
         * @param {String} refsInfo Direcciona al Refs del componente hijo ubicado en la variable childsRefs, ejemplo "principal"
         */
        listControl(refsInfo,inputTarget){
            //Controla el estado de la lista
            if(this.childsRefs[refsInfo.objectRef][refsInfo.nameRef] !== document.activeElement){
                this.dropDownControl[inputTarget].noInput = false //Lo desactiva si el input pierde el focus
            }else{
                this.dropDownControl[inputTarget].noInput = true //Lo activa si el input tiene focus y no esta vacio
                if(this[refsInfo.inputRef].length == 0) this.dropDownControl[inputTarget].noInput = false //Desactiva si input esta vacio
            }
        },
        /**
         * Metodo que si la informacion seleccionada coincide con un valor en la tabla
         * @param {Object} objectTarget Objeto de configuracion que debe recibir las siguientes propiedades:
         * @property {String} table: Indica a que tabla es redireccionando
         * @property {String} column: Columna de la tabla que va a comparar
         * @property {String} inputValid: Variable de control de validaciones
         * @property {String} errorInput: Variable que controla el mensaje de error
         * @param {String} valueToCompare Captura la informacion del string a comparar
         */
        validateTable(objectTarget, valueToCompare){
            try{
                const infoDTO = this.dataSelect[objectTarget.table].filter(columns => { 
                    return columns[objectTarget.column].toString().toLowerCase().includes(valueToCompare.toString().toLowerCase())})
                //Si no pertenece, error
                if(infoDTO.length == 0) throw 'NoRefFound';
                //En caso que pertezca, desactivamos el mensaje de error
                if(infoDTO.length != 0) this.messages.error[objectTarget.errorInput] = '';
            }catch (errorMessage){
                this.submitButton[objectTarget.inputValid] = false
                this.messages.error[objectTarget.errorInput] = Exceptions.CatchWarning(errorMessage)
            }
        },
        //Insertado de fecha
        insertDate(dateDTO) { this.inputHiringDate = `${dateDTO.year}-${dateDTO.month}-${dateDTO.day}`; },
        /**
         * Metodo que se encarga sumar las horas totales del proyecto
         * @param {*} infoDepartments Objeto donde esta almacenada la informacion de las divisiones
         */
        totalHours(infoDepartments){
            this.inputHoursAssigned = 0 //Reiniciamos el contador
            //Recorremos el arrray y sumamos en el v-model de horas totales
            for (let cursorDeparment = 0; cursorDeparment < infoDepartments.length; cursorDeparment++) {
                const numberValid = new RegExp('^([0-9]*)$');
                const testNumber = numberValid.test(infoDepartments[cursorDeparment].hoursAssigned)
                //Si es un numero, sumamos
                if(testNumber && infoDepartments[cursorDeparment].hoursAssigned.length != 0) 
                    this.inputHoursAssigned = parseInt(this.inputHoursAssigned) + parseInt(infoDepartments[cursorDeparment].hoursAssigned); 
            }
        }
    },
}