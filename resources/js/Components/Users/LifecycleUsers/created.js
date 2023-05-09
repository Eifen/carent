import { Validate } from "@/Models/ValidateModel";
import { Exceptions } from "@/Excepciones/Excepciones";
const LIMITSTRING = { NAME: 20 }
/**
 * Created para el componente usuarios
 * @param {*} self Almacena la data definida en el componente padre
 */
export const createdMixin = (self) => 
{
        //Asignamos las clases
        self.formClass.form = self.formClass.container + '-form'
        self.formClass.legend = self.formClass.form + "-legends"
        self.formClass.fieldset = self.formClass.form + "-fieldset"
        self.formClass.button = self.formClass.form + "-button"
        self.formClass.disableButton = self.formClass.button + "-disable"
        self.formClass.requiredTitle = self.formClass.form + "-title"
        self.formClass.requiredField = self.formClass.requiredTitle + "-field"

        //Definimos los watchers
        self.inputWatchers =
        [{
            //Validaciones de STRING con longitud menor o igual que LIMITSTRING.NAME
            propiedades: ['inputFirstname','inputSecondname','inputLastname','inputLastSecondname'],
            watch: (newString) =>
            {
                try{
                    const validateString = Validate.String(newString,LIMITSTRING.NAME);
                    //First Name se activó pero no cumple con el formato
                    if(self.inputFirstname == newString && !validateString.response)
                    throw {"message": validateString.message, "errorInput": "firstnameError", "input":"inputFirstname"};

                    //Second Name se activó pero no cumple con el formato
                    if(self.inputSecondname == newString && !validateString.response)
                    throw {"message": validateString.message, "errorInput": "secondnameError", "input":"inputSecondname"};

                    //Last Name se activó pero no cumple con el formato
                    if(self.inputLastname == newString && !validateString.response)
                    throw {"message": validateString.message, "errorInput": "lastnameError", "input":"inputLastname"};

                    //Last Second Name se activó pero no cumple con el formato
                    if(self.inputLastSecondname == newString && !validateString.response)
                    throw {"message": validateString.message, "errorInput": "lastsecondnameError", "input":"inputLastSecondname"};

                    //Si paso las validaciones
                    //Activamos la bandera del primer nombre y primer apellido
                    if(self.inputFirstname.length > 0 && self.inputFirstname.length <= LIMITSTRING.NAME){
                        self.messages.form.firstnameError = '';
                        self.submitButton.firstnameValid = true;
                    }
                    if(self.inputLastname.length > 0 && self.inputLastname.length <= LIMITSTRING.NAME){
                        self.messages.form.lastnameError = '';
                        self.submitButton.lastnameValid = true;
                    }
                    //Datos opcionales
                    if(self.inputSecondname.length > 0 && self.inputSecondname.length <= LIMITSTRING.NAME) self.messages.form.secondnameError = '';
                    if(self.inputLastSecondname.length > 0 && self.inputLastSecondname.length <= LIMITSTRING.NAME) self.messages.form.lastsecondnameError = '';

                    //Desactivamos las banderas si el valor es vacio o si es mayor al limite
                    if(self.inputFirstname.length == 0 || self.inputFirstname.length > LIMITSTRING.NAME) self.submitButton.firstnameValid = false;
                    if(self.inputLastname.length == 0 || self.inputLastname.length > LIMITSTRING.NAME) self.submitButton.lastnameValid = false;

                }catch(errorJSON)
                {
                    //Desactivamos las banderas
                    if(errorJSON.input == 'inputFirstname') self.submitButton.firstnameValid = false;
                    if(errorJSON.input == 'inputLastname') self.submitButton.lastnameValid = false;
                    //Pasamos el error
                    self.messages.form[errorJSON.errorInput] = Exceptions.CatchWarning(errorJSON.message) + self[errorJSON.input].length +`(${LIMITSTRING.NAME})`;
                }
            }
        },
        {
            //Validaciones de fechas
            propiedades: ['inputBirthday','inputIngreso','inputEgreso'],
            watch: (newDate) =>
            {
                try{
                    const validateDate = Validate.Date(newDate)
                    //Fecha de nacimiento
                    if(self.inputBirthday == newDate && !validateDate.response && newDate.length >= 10)
                    throw {"message":validateDate.message,"errorInput":"birthdayError","input":"inputBirthday"};
                    //Fecha de ingreso
                    if(self.inputIngreso == newDate && !validateDate.response && newDate.length >= 10)
                    throw {"message":validateDate.message,"errorInput":"ingresoError","input":"inputIngreso"};
                    //Fecha de egreso
                    if(self.inputEgreso == newDate && !validateDate.response && newDate.length >= 10)
                    throw {"message":validateDate.message,"errorInput":"egresoError","input":"inputEgreso"};
                    //Si no presenta ningún error, es que cumple con el formato

                    //Desactivamos las banderas y el error si la fecha es vacia
                    if(self.inputBirthday == '' || self.inputBirthday.length < 10) self.submitButton.birthdayValid = false;

                    //Deshabilitamos los errores
                     if(self.inputBirthday == '' || self.inputBirthday.length <= 10) self.messages.form.birthdayError = '';
                    if(self.inputIngreso == '' || self.inputIngreso.length <= 10) self.messages.form.ingresoError = '';
                    if(self.inputEgreso == '' || self.inputEgreso.length <= 10) self.messages.form.egresoError = '';

                    //Activamos la bandera de la fecha de Nacimiento
                    if(self.inputBirthday == newDate && validateDate.response && self.inputBirthday.length == 10)
                    self.submitButton.birthdayValid = true;

                }catch(errorJSON)
                {
                    //Desactivamos la bandera
                    if(errorJSON.input == 'inputBirthday') self.submitButton.birthdayValid = false;
                    //Pasamos el error
                    self.messages.form[errorJSON.errorInput] = Exceptions.CatchWarning(errorJSON.message);
                }
            }
        },
        {
            //Validaciones de Correo
            propiedades: ['inputFirstEmail','inputSecondEmail'],
            watch: () =>
            {
                try{
                    const validateFirstEmail = Validate.Email(self.inputFirstEmail);
                    const validateSecondEmail = Validate.Email(self.inputSecondEmail);
                    //Verificamos la gestión de errores
                    switch(true)
                    {
                        //Ambos correos
                        case (!validateFirstEmail.response && !validateSecondEmail.response)
                            && (self.inputFirstEmail.length > 0 && self.inputSecondEmail.length > 0):
                            throw {"message":validateFirstEmail.message,"target":"Both"};
                        //Correo principal
                        case !validateFirstEmail.response && self.inputFirstEmail.length > 0:
                            throw {"message":validateFirstEmail.message,"target":"First"};
                        
                        //Correo Secundario
                        case !validateSecondEmail.response && self.inputSecondEmail.length > 0:
                            throw {"message":validateSecondEmail.message,"target":"Second"};
                    }

                    //Borramos el error
                    if(self.inputSecondEmail.length == 0 || validateSecondEmail.response) self.messages.form.secondemailError = '';

                    //Desactivamos la bandera si el input esta vacio
                    if(self.inputFirstEmail.length == 0 || validateFirstEmail.response)self.messages.form.firstemailError = '';
                    if(self.inputFirstEmail.length == 0) self.submitButton.firstemailValid = false;

                    //Activamos la bandera del correo principal
                    if(validateFirstEmail.response) self.submitButton.firstemailValid = true;
                }catch(errorJSON)
                {
                    switch(errorJSON.target){
                        case 'First':
                            self.messages.form.firstemailError = Exceptions.CatchWarning(errorJSON.message);
                            self.messages.form.secondemailError = '';
                            self.submitButton.firstemailValid = false;
                            break;
                        case 'Second':
                            self.messages.form.secondemailError = Exceptions.CatchWarning(errorJSON.message);
                            self.messages.form.firstemailError = '';
                            break;
                        case 'Both':
                            self.messages.form.firstemailError = Exceptions.CatchWarning(errorJSON.message);
                            self.submitButton.firstemailValid = false;
                            self.messages.form.secondemailError = Exceptions.CatchWarning(errorJSON.message);
                            break;
                    }
                }
            }
        },
        {
            //Validaciones de telefono
            propiedades:['inputFirstPhone','inputSecondPhone'],
            watch: (newPhone,oldPhone) =>
            {
                try{
                    //00001112222. Colocamos ese formato antes de validar
                    const phoneDTO = newPhone.replace('(','').replace(')','').replace(/-/g,'');
                    const validatePhone = Validate.Phone(phoneDTO);

                    //Se activo telefono principal
                    if(self.inputFirstPhone == newPhone && !validatePhone.response)
                    throw "inputFirstPhone";

                    //Se activo telefono secundario
                    if(self.inputSecondPhone == newPhone && !validatePhone.response)
                    throw "inputSecondPhone";

                    //Desactivamos la bandera si esta vacio o no tiene la longitud adecuada
                    if(self.inputFirstPhone == '' || self.inputFirstPhone.length != 15) self.submitButton.firstphoneValid = false;

                    //Creamos el formato
                    if(self.inputFirstPhone == newPhone && validatePhone.response)
                    {
                        self.inputFirstPhone = validatePhone.message;
                        //Activamos la bandera del telefono principal
                        if(newPhone.length == 15) self.submitButton.firstphoneValid = true;
                    }
                    if(self.inputSecondPhone == newPhone && validatePhone.response) self.inputSecondPhone = validatePhone.message;

                }catch(error)
                {
                    //Desactivamos la bandera
                    if(error == 'inputFirstPhone') self.submitButton.firstphoneValid = false;
                    //Colocamos el valor anterior
                    self[error] = oldPhone;
                }
            }
        }]

        //Axios Request
        axios.post('/usuarios/getParamsInit')
        .then(request => {
            if(request.status !== 200) throw request;

            //Si sincroniza con cada una de las listas
            self.typeDocument = request.data.TiposDocumento
            self.stateData = request.data.StatesUsuario
            self.divisionData = request.data.Divisiones
            self.cargoData = request.data.Cargos
            self.statusData = request.data.StatusUsuario
            //Si la bandera de edit esta activa, pasamos la data almacenada en el cliente
            if(self.isEdit) self.$emit('init-user');
        })
        .catch(error => {
            console.error(error)
        })
}