import { Validate } from "@/Models/ValidateModel";
import { Exceptions } from "@/Excepciones/Excepciones";
/**
 * Almacena los metodos del componente de usuarios
 */
export const userMethods = {
    methods: {
        //Obtenemos la diferentes datas
        insertDate(dateDTO) { this.inputBirthday = `${dateDTO.year}-${dateDTO.month}-${dateDTO.day}`; },
        insertIngreso(dateDTO) { this.inputIngreso = `${dateDTO.year}-${dateDTO.month}-${dateDTO.day}`; },
        insertEgreso(dateDTO) { this.inputEgreso = `${dateDTO.year}-${dateDTO.month}-${dateDTO.day}`; },
        /**
         * Metodo que habilita el input del documento de identidad
         * @param {*} changeValue Valor obtenido desde el componente DataPrincipal con el cambio del select
         */
        enableInput(changeValue) {
            this.getTargetTypeDocument = changeValue
            this.inputSelect = `${this.getTargetTypeDocument}-`
        },
        /**
         * Metodo que valida los watchers de tipo String
         * @param {Number} limitString Tamaño máximo de validación
         * @param {String} varInput Selecciona la variable de v-model del input del watcher
         * @param {String} stringToValidate Almacena la data a validar
         * @param {String} varError Selecciona el mensaje de error del input del watcher
         * @param {[boolean,string]} validInput En caso de que sea un campo obligatorio, Almacenara una tupla de tipo [boolean,string]
         * Donde boolean determina si el input es obligatorio (True|False) y string selecciona
         * la variable de control de validación del input Señalado
         */
        validateString(limitString,stringToValidate,varInput,varError,validInput){
            try {
                const validate = Validate.String(stringToValidate,limitString)
                if(!validate.response) throw validate.message;
                //Si pasa las validaciones activamos la bandera
                if(this[varInput].length <= limitString) this.messages.form[varError] = '';
                if(validInput[0] && validate.response) this.submitButton[validInput[1]] = true;
                //Desactivamos la bandera si el valor es 0 o mayor al limite
                if((validInput[0] && this[varInput].length == 0) || (validInput[0] && this[varInput].length > limitString)) this.submitButton[validInput[1]] = false;
            } catch (error) {
                //Desactivamos su bandera en caso de que sea un valor obligatorio
                if(validInput[0]) this.submitButton[validInput[1]] = false;
                this.messages.form[varError] = Exceptions.CatchWarning(error) + stringToValidate.length + `(${limitString})`;
            }
        },
        /**
         * Metodo que valida las distintas fechas
         * @param {Date} dateToValidate Fecha en formato 'YYYY-mm-dd'
         * @param {String} varInput indica la variable que se usa como v-model
         * @param {String} varError Indica la variable donde se almacena el error del v-model 
         * @param {[Boolean,String]} validInput Tupla [boolean,string] que indica si es un campo obligatorio, y cual es su
         * variable de validación.
         */
        validateDate(dateToValidate,varInput,varError,validInput){
            try {
                const validate = Validate.Date(dateToValidate)
                if(!validate.response && dateToValidate.length >= 10) throw validate.message;
                //Pasa las validaciones
                if(this[varInput].length == 0 || this[varInput].length <= 10) this.messages.form[varError] = '';
                if(validInput[0] && validate.response) this.submitButton[validInput[1]] = true;
                //Desactivamos las banderas
                if((this[varInput].length == 0 && validInput[0]) || (this[varInput].length < 10 && validInput[0])) this.messages.form[varError] = '';
            } catch (error) {
                if(validInput[0]) this.submitButton[validInput[1]] = false;
                this.messages.form[varError] = Exceptions.CatchWarning(error)
            }
        },
        /**
         * Metodo que valida los distintos correos
         * @param {String} emailToValidate El correo en formato string a validar
         * @param {String} varInput indica la variable que se usa como v-model
         * @param {String} varError Indica la variable donde se almacena el error del v-model 
         * @param {[Boolean,String]} validInput Tupla [boolean,string] que indica si es un campo obligatorio, y cual es su
         * variable de validación.*/
        validateEmail(emailToValidate,varInput,varError,validInput){
            try{
                const validate = Validate.Email(emailToValidate);
                if(!validate.response && this[varInput].length > 0) throw validate.message;
                //Passamos las validaciones
                if(this[varInput].length == 0 || validate.response) this.messages.form[varError] = ''
                if(validate.response && validInput[0]) this.submitButton[validInput[1]] = true;
                //Desactivamos la bandera
                if(this[varInput] == 0 && validInput[0]) this.submitButton[validInput[1]] = false;
            }catch (error){
                //Desactivamos las banderas
                if(validInput[0]) this.submitButton[validInput[1]] = false;
                this.messages.form[varError] = Exceptions.CatchWarning(error)
            }
        },
        /**
         * Metodo que valida los números telefonicos
         * @param {String} phoneToValidate numero de telefono en formato (0000)-1112222
         * @param {String} phoneOldValue valor anterior antes del cambio en el v-model
         * @param {String} varInput indica la variable que se usa como v-model
         * @param {[Boolean,String]} validInput Tupla [boolean,string] que indica si es un campo obligatorio, y cual es su
         * variable de validación.
         */
        validatePhone(phoneToValidate,phoneOldValue,varInput,validInput){
            try {
                //Transformamos el numero a un formato 00001112222
                const phoneDTO = phoneToValidate.replace('(','').replace(')','').replace(/-/g,'');
                const validate = Validate.Phone(phoneDTO)
                if(!validate.response) throw validate.message;
                //Pasamos las validaciones, creando el formato del número
                if(validate.response){
                    this[varInput] = validate.message
                    if(validInput[0] && this[varInput].length == 15) this.submitButton[validInput[1]] = true;
                }
                //Desactivamos la bandera
                if((validInput[0] && this[varInput].length == 0) || (validInput[0] && this[varInput].length != 15)) this.submitButton[validInput[1]] = false;
            } catch (error) {
                //Desactivamos las banderas
                if(validInput[0]) this.submitButton[validInput[1]] = false;
                this[varInput] = phoneOldValue; //Volvemos al valor anterior
            }
        },
        /**
         * Metodo que envia la data del formulario  al componente padre
         */
        DTOEmit() {
            //Pasamos los parametros a analizar
            let paramsToEmit =
            {
                "FirstName": this.inputFirstname,
                "SecondName": this.inputSecondname,
                "LastName": this.inputLastname,
                "SecondLastName": this.inputLastSecondname,
                "Cedula": this.inputSelect,
                "Birthday": this.inputBirthday,
                "Code": this.inputCode,
                "FirstEmail": this.inputFirstEmail,
                "SecondEmail": this.inputSecondEmail,
                "FirstPhone": this.inputFirstPhone,
                "SecondPhone": this.inputSecondPhone,
                "IdParish": this.inputParroquiaSelect,
                "IdCargo": this.inputCargoSelect,
                "IdDivision": this.inputDivisionSelect,
                "DateIngreso": this.inputIngreso,
            }

            //Preparamos los parametros de update en caso de actualizar
            let paramsToUpdate =
            {
                "DateEgreso": this.inputEgreso,
                "Status": this.inputStatusSelect
            }

            //Si estamos en edición, unimos los dos objetos
            if (this.isEdit) paramsToEmit = { ...paramsToEmit, ...paramsToUpdate }
            this.$emit('submit-form', paramsToEmit);
        }
    }
}
