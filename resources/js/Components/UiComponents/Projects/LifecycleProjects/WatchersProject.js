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
        inputClientAssociated(searchClient){
            //Activamos la Lista
            this.listControl({
                nameRef: this.dropDownControl.clients.ref,
                objectRef: 'principal',
                inputRef: 'inputClientAssociated'
            },'clients')
            //Tras aplicar el control de la lista, procedemos a verificar que cumpla con el formato correcto
            this.validateString({
                "limitString": this.LimitString.NAME,
                "stringToValidate": searchClient,
                "varInput":"inputClientAssociated",
                "varError": 'clientAssociatedError',
                "validInput": [true, 'clientAssociatedValid']
            })
            //Luego verificamos si corresponde a un dato en la tabla
            this.validateTable({
                table:'clients',
                column:'bussiness_name',
                inputValid:'clientAssociatedValid',
                errorInput:'clientAssociatedError'},searchClient)
        },
        inputManagerAssociated(searchManager){
            //Activamos la Lista
            this.listControl({
                nameRef: this.dropDownControl.manager.ref,
                objectRef: 'distribution',
                inputRef: 'inputManagerAssociated'
            },'manager')
            //Tras aplicar el control de la lista, procedemos a verificar que cumpla con el formato correcto
            this.validateString({
                "limitString": this.LimitString.NAME,
                "stringToValidate": searchManager,
                "varInput":"inputManagerAssociated",
                "varError": 'managerError',
                "validInput": [true, 'managerValid']
            })
            //Luego verificamos si corresponde a un dato en la tabla
            this.validateTable({
                table:'managers',
                column:'user_name',
                inputValid:'managerValid',
                errorInput:'managerError'},searchManager)
        },
        inputPartnerAssociated(searchPartner){
            //Activamos la Lista
            this.listControl({
                nameRef: this.dropDownControl.partner.ref,
                objectRef: 'distribution',
                inputRef: 'inputPartnerAssociated'
            },'partner')
            //Tras aplicar el control de la lista, procedemos a verificar que cumpla con el formato correcto
            this.validateString({
                "limitString": this.LimitString.NAME,
                "stringToValidate": searchPartner,
                "varInput":"inputPartnerAssociated",
                "varError": 'partnerError',
                "validInput": [true, 'partnerValid']
            })
            //Luego verificamos si corresponde a un dato en la tabla
            this.validateTable({
                table:'partners',
                column:'user_name',
                inputValid:'partnerValid',
                errorInput:'partnerError'},searchPartner)
        },
        inputQualityPartnerAssociated(searchQualityPartner){
            //Activamos la Lista
            this.listControl({
                nameRef: this.dropDownControl.qualityPartner.ref,
                objectRef: 'distribution',
                inputRef: 'inputQualityPartnerAssociated'
            },'qualityPartner')
            //Tras aplicar el control de la lista, procedemos a verificar que cumpla con el formato correcto
            this.validateString({
                "limitString": this.LimitString.NAME,
                "stringToValidate": searchQualityPartner,
                "varInput":"inputQualityPartnerAssociated",
                "varError": 'qualityPartnerError',
                "validInput": [true, 'partnerValid']
            })
            //Luego verificamos si corresponde a un dato en la tabla
            this.validateTable({
                table:'partners',
                column:'user_name',
                inputValid:'qualityPartnerValid',
                errorInput:'qualityPartnerError'},searchQualityPartner)
        },             
    }
}