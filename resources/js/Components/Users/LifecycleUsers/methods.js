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
                //Si pasa las validaciones
                if(this[varInput].length <= limitString) this.messages.form[varError] = '';
                if(validInput[0]) this.submitButton[1] = true;
            } catch (error) {
                //Desactivamos su bandera en caso de que sea un valor obligatorio
                if(validInput[0]) this.submitButton[1] = false;
                this.messages.form[varError] = Exceptions.CatchWarning(error) + stringToValidate.length + `(${limitString})`;
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
