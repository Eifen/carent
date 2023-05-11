import { Validate } from "@/Models/ValidateModel";
import { Exceptions } from "@/Excepciones/Excepciones";

export const clientMethods = 
{
    methods: 
    {
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
                if(this[varInput].length <= limitString) this.messages.error[varError] = '';
                if(validInput[0] && validate.response) this.submitButton[validInput[1]] = true;
                //Desactivamos la bandera si el valor es 0 o mayor al limite
                if((validInput[0] && this[varInput].length == 0) || (validInput[0] && this[varInput].length > limitString)) this.submitButton[validInput[1]] = false;
            } catch (error) {
                //Desactivamos su bandera en caso de que sea un valor obligatorio
                if(validInput[0]) this.submitButton[validInput[1]] = false;
                this.messages.error[varError] = Exceptions.CatchWarning(error) + stringToValidate.length + `(${limitString})`;
            }
        },
        /**
         * Metodo que envia la data del formulario  al componente padre
         */
        ClientEmit() {
            //Pasamos los parametros a analizar
            let paramsToEmit =
            {
                "IdSocio": this.inputSocioSelect, 
                "IdSector": this.inputSectorSelect, 
                "IdServicio": this.inputServicioSelect,
                "IdPais": this.inputPaisSelect, 
                "Nit": this.inputNit.toString(), 
                "Rif": this.inputRif.toString(), 
                "Telefono": this.inputTelefono.replace('-',""),
                "RazonSocial": this.inputRazonSocial.toString(),
                "Direccion":this.inputDireccion.toString(),
                "EmailFiscal": this.inputFirstEmail.toString(),
                "PaginaWeb": this.inputWeb.toString(),
            }

            //Preparamos los parametros de update en caso de actualizar
            let paramsToUpdate =
            {
                "Status": this.inputStatusSelect
            }

            //Si estamos en edición, unimos los dos objetos
            if (this.isEdit) paramsToEmit = { ...paramsToEmit, ...paramsToUpdate }
            this.$emit('submit-form', paramsToEmit);
        }
    }
}