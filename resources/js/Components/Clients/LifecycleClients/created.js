import { Validate } from "@/Models/ValidateModel"
import { Exceptions } from "@/Excepciones/Excepciones"

/**
 * Hook created para formulario de clientes
 * @param {*} self Hereda la data de su padre
 */
export const createdMixin = (self) => {
    //Asignamos las clases
    self.formClass.form = self.formClass.container + '-form'
    self.formClass.legend = self.formClass.form + "-legends"
    self.formClass.fieldset = self.formClass.form + "-fieldset"
    self.formClass.button = self.formClass.form + "-button"
    self.formClass.disableButton = self.formClass.button + "-disable"
    self.formClass.requiredTitle = self.formClass.form + "-title"
    self.formClass.requiredField = self.formClass.requiredTitle + "-field"

    //Espacio de creacion de Watchers globales
    self.inputWatchers =
    [{
        propiedades: ['inputRazonSocial'],
        watch: (newString) =>
        {
            try{
                const validateString = Validate.String(newString,self.LimitString.NAME);
                //Razon Social
                if(self.inputRazonSocial == newString && !validateString.response)
                throw {"message": validateString.message, "errorInput": "razonSocialError", "input": "inputRazonSocial"};
                //Control de banderas
                if(self.inputRazonSocial.length != 0 && validateString.response) self.submitButton.razonSocialValid = true;
                if(self.inputRazonSocial.length == 0) self.submitButton.razonSocialValid = false;
                //-
                //Desactivamos los mensajes de error
                if(self.inputRazonSocial.length > 0) self.messages.error.razonSocialError = '';

            }catch (jsonCatch){
                //Capuramos el error
                self[jsonCatch.input] = '';
                self.messages.error[jsonCatch.errorInput] = Exceptions.CatchWarning(jsonCatch.message) + self.LimitString.NAME;
                //Desactivamos Banderas
                if(jsonCatch.input == 'inputRazonSocial') self.submitButton.razonSocialValid = false;
            }
        }
    }]

    //Axios
    axios.post('/clientes/getParamsInits')
    .then(request => {
        if(request.status !== 200) throw request;

        //Si pasa el control, procedemos a insertarlo
        self.dataSelect.socio = request.data.dataSocio
    })
    .catch(error => {
        console.error(error);
    });
}
