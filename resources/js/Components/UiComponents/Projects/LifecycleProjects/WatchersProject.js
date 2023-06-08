import { Validate } from "@/Models/ValidateModel";
import { Exceptions } from "@/Excepciones/Excepciones";

export const projectWatchers = 
{
    watch: 
    {
        inputStatusSelect(actualTarget){
            //Verificamos que la seleccion de status del proyecto sea correcta
            if(actualTarget >= 1){
                this.submitButton.statusValid = true
            }else{
                this.submitButton.statusValid = false
            }
        },
        inputProjectDescription(newString){
            this.validateString({
                "limitString": this.LimitString.DESCRIPTION,
                "stringToValidate": newString,
                "varInput":'inputProjectDescription',
                "varError": 'projectDescriptionError',
                "validInput": [true, 'projectDescriptionValid']})
        },
        inputClientAssociated(searchCliente){
            console.log(searchCliente)
        }
    }
}