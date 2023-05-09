import { Validate } from "@/Models/ValidateModel";
import { Exceptions } from "@/Excepciones/Excepciones";

export const clientWatchers =
{
    watch:
    {
        //Rif
        inputNit(newValue,oldValue)
        {
            try {
                const nuevoNit = Validate.Number(newValue)
                //Validacion deL NIT
                if(!nuevoNit.response || (nuevoNit.response && newValue.length > 11)) throw nuevoNit;
                this.inputNit = newValue;
            } catch (error) {
                this.inputNit = oldValue;
            }
        },
        //Seleccion de Socio
        inputSocioSelect(newValue)
        {
            if(newValue != 0) this.submitButton.selectSocio = true;
            if(newValue == 0) this.submitButton.selectSocio = false;
        },
        //Rif
        inputRif(newValue,oldValue)
        {
            try {
                const nuevoRif = Validate.RifFormat(newValue);
                //Primer nivel de validación
                //message[2] = Es el segundo intervalo del regex en formato ejemplo: V123456789 (123456789)
                if(!nuevoRif.response && newValue.length <= 15 ) throw nuevoRif.message;
                if(nuevoRif.response && nuevoRif.message[2].length > 14 || (!nuevoRif.response && newValue.length > 15)) throw 'OutRange';
                //Paso las validaciones
                this.submitButton.rifValid = true;
                this.messages.error.rifError = '';
                this.inputRif = newValue.toUpperCase();
            } catch (error) {
                //Capturamos el error
                error == 'OutRange'
                ? this.messages.error.rifError = Exceptions.CatchWarning(error) + this.LimitString.RIF
                : this.messages.error.rifError = Exceptions.CatchWarning(error);
                this.submitButton.rifValid = false;
            }
        },
        //Deep Watchers
        submitButton: {
            deep:true,
            handler(checkValid)
            {
                let contValid = 0; //Contar cada vez que la propiedad sea true
                for(const field in checkValid){
                    if(field.toString() != 'isValid' && checkValid[field] === true) contValid++;
                }

                //Revisamos que contenga toda la data
                if(contValid == Object.keys(checkValid).length -1){
                    this.submitButton.isValid = true
                }else{ this.submitButton.isValid = false }
            }
        }
    }
}
