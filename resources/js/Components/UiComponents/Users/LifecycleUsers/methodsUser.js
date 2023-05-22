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
         * @param {Object} dateFilter Object  JSON con el siguiente formato:
         * {
         * @prop1
         * "dateToValidate": Fecha en formato 'YYYY-mm-dd',
         * @prop2
         * "varInput": indica la variable que se usa como v-model,
         * @prop3
         * "varError": Indica la variable donde se almacena el error del v-model,
         * @prop4
         * "validInput": Tupla [boolean,string] que indica si es un campo obligatorio, y cual es su variable de validación. }
         */
        validateDate(dateFilter){
            try {
                const validate = Validate.Date(dateFilter.dateToValidate)
                if(!validate.response && dateFilter.dateToValidate.length >= 10) throw validate.message;
                //Pasa las validaciones
                if(this[dateFilter.varInput].length == 0 || this[dateFilter.varInput].length <= 10) this.messages.form[dateFilter.varError] = '';
                if(dateFilter.validInput[0] && validate.response) this.submitButton[dateFilter.validInput[1]] = true;
                //Desactivamos las banderas
                if((this[dateFilter.varInput].length == 0 && dateFilter.validInput[0]) || (this[dateFilter.varInput].length < 10 && dateFilter.validInput[0])) this.messages.form[dateFilter.varError] = '';
            } catch (error) {
                if(dateFilter.validInput[0]) this.submitButton[dateFilter.validInput[1]] = false;
                this.messages.form[dateFilter.varError] = Exceptions.CatchWarning(error)
            }
        },
        /**
         * Metodo que valida los watchers de tipo String
         * @param {Object} emailFilter Object  JSON con el siguiente formato:
         * {
         * @prop1
         * "emailToValidate": El correo en formato string a validar,
         * @prop2
         * "varInput": indica la variable que se usa como v-model,
         * @prop3
         * "varError": Indica la variable donde se almacena el error del v-model,
         * @prop4
         * "validInput": Tupla [boolean,string] que indica si es un campo obligatorio, y cual es su variable de validación. }
         */
        validateEmail(emailFilter){
            try{
                const validate = Validate.Email(emailFilter.emailToValidate);
                if(!validate.response && this[emailFilter.varInput].length > 0) throw validate.message;
                //Passamos las validaciones
                if(this[emailFilter.varInput].length == 0 || validate.response) this.messages.form[emailFilter.varError] = ''
                if(validate.response && emailFilter.validInput[0]) this.submitButton[emailFilter.validInput[1]] = true;
                //Desactivamos la bandera
                if(this[emailFilter.varInput] == 0 && emailFilter.validInput[0]) this.submitButton[emailFilter.validInput[1]] = false;
            }catch (error){
                //Desactivamos las banderas
                if(emailFilter.validInput[0]) this.submitButton[emailFilter.validInput[1]] = false;
                this.messages.form[emailFilter.varError] = Exceptions.CatchWarning(error)
            }
        },
        /**
         * Metodo que valida los watchers de tipo String
         * @param {Object} phoneFilter Object  JSON con el siguiente formato:
         * {
         * @prop1
         * "phoneToValidate": numero de telefono en formato (0000)-1112222,
         * @prop2
         * "phoneOldValue": valor anterior antes del cambio en el v-model,
         * @prop3
         * "varInput": indica la variable que se usa como v-model,
         * @prop4
         * "validInput": Tupla [boolean,string] que indica si es un campo obligatorio, y cual es su variable de validación. }
         */
        validatePhone(phoneFilter){
            try {
                //Transformamos el numero a un formato 00001112222
                const phoneDTO = phoneFilter.phoneToValidate.replace('(','').replace(')','').replace(/-/g,'');
                const validate = Validate.Phone(phoneDTO)
                if(!validate.response) throw validate.message;
                //Pasamos las validaciones, creando el formato del número
                if(validate.response){
                    this[phoneFilter.varInput] = validate.message
                    if(phoneFilter.validInput[0] && this[phoneFilter.varInput].length == 15) this.submitButton[phoneFilter.validInput[1]] = true;
                }
                //Desactivamos la bandera
                if((phoneFilter.validInput[0] && this[phoneFilter.varInput].length == 0) || (phoneFilter.validInput[0] && this[phoneFilter.varInput].length != 15)) this.submitButton[phoneFilter.validInput[1]] = false;
            } catch (error) {
                //Desactivamos las banderas
                if(phoneFilter.validInput[0]) this.submitButton[phoneFilter.validInput[1]] = false;
                this[phoneFilter.varInput] = phoneFilter.phoneOldValue; //Volvemos al valor anterior
            }
        },
        /**
         * Metodo que envia la data del formulario  al componente padre
         */
        userEmit() {
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
