import { Validate } from "@/Models/ValidateModel"
import { Exceptions } from "@/Excepciones/Excepciones"
const LIMITSTRING = { NAME: 50, DIR: 200, WEB: 100}

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
