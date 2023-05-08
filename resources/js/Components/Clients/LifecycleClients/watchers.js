import { Validate } from "@/Models/ValidateModel";
import { Exceptions } from "@/Excepciones/Excepciones";

export const clientWatchers =
{
    watch:
    {
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
        inputSocioSelect(newValue)
        {
            if(newValue != 0) this.submitButton.selectSocio = true;
            if(newValue == 0) this.submitButton.selectSocio = false;
        },
        //TODO Colocar el WATCHER DEL RIF
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