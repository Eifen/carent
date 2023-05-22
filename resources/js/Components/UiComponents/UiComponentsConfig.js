import { Validate } from "@/Models/ValidateModel";
import { Exceptions } from "@/Excepciones/Excepciones";

export const dataMixin =
{
    data() {
        return {
            formClass:
            {
                container: 'dashboard-form-container',
                form: '',
                legend: '',
                fieldset: '',
                button: '',
                disableButton: '',
                successValidation: 'form-SuccessInput',
                failureValidation: 'form-ErrorInput',
                requiredTitle: '',
                requiredField: '',
                select: 'form-select-container',
                empleadoFieldset: '',
            }, //Controla los estilos del formulario
        }
    }
}

export const methodsGlobalMixin =
{
    methods:{
        /**
         * Metodo que valida los watchers de tipo String
         * @param {Object} stringFilter Object  JSON con el siguiente formato:
         * {
         * @prop1
         * "limitString": el numero máximo de caracteres,
         * @prop2
         * "stringToValidate": string a revisar,
         * @prop3
         * "varInput": indica la variable que se usa como v-model,
         * @prop4
         * "varError": Indica la variable donde se almacena el error del v-model
         * @prop5
         * "validInput": Tupla [boolean,string] que indica si es un campo obligatorio, y cual es su variable de validación. }
         */
        validateString(stringFilter){
            try {
                const validate = Validate.String(stringFilter.stringToValidate,stringFilter.limitString)
                if(!validate.response) throw validate.message;
                //Si pasa las validaciones activamos la bandera
                if(this[stringFilter.varInput].length <= stringFilter.limitString) this.messages.error[stringFilter.varError] = '';
                if(stringFilter.validInput[0] && validate.response) this.submitButton[stringFilter.validInput[1]] = true;
                //Desactivamos la bandera si el valor es 0 o mayor al limite
                if((stringFilter.validInput[0] && this[stringFilter.varInput].length == 0) || (stringFilter.validInput[0] && this[stringFilter.varInput].length > stringFilter.limitString)) this.submitButton[stringFilter.validInput[1]] = false;
            } catch (error) {
                //Desactivamos su bandera en caso de que sea un valor obligatorio
                if(stringFilter.validInput[0]) this.submitButton[stringFilter.validInput[1]] = false;
                this.messages.error[stringFilter.varError] = Exceptions.CatchWarning(error) + stringFilter.stringToValidate.length + `(${stringFilter.limitString})`;
            }
        }
    }
}

/**
 * Funcion que se encarga de asignar las clases del formulario
 * @param {*} scope Captura la data() del componente que le llama
 */
export const classConfig = (scope) => {
    //Asignamos las clases
    scope.formClass.form = scope.formClass.container + '-form'
    scope.formClass.legend = scope.formClass.form + "-legends"
    scope.formClass.fieldset = scope.formClass.form + "-fieldset"
    scope.formClass.button = scope.formClass.form + "-button"
    scope.formClass.disableButton = scope.formClass.button + "-disable"
    scope.formClass.requiredTitle = scope.formClass.form + "-title"
    scope.formClass.requiredField = scope.formClass.requiredTitle + "-field"
}
