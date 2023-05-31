import { Validate } from "@/Models/ValidateModel";
import { Exceptions } from "@/Excepciones/Excepciones";
/**
 * CLase que almacena todos los Watcher y lo extiende al componente
 */
export const userWatchers =
{
    watch: {
        //Watch para el update
        dataEdit(newEdit) {
            this.inputFirstname = newEdit.first_name
            this.inputSecondname = newEdit.second_name
            this.inputLastname = newEdit.first_surname
            this.inputLastSecondname = newEdit.second_surname
            this.inputSelect = newEdit.identity_abbreviation + "-" + newEdit.identity_number
            this.inputDocumentoSelect = newEdit.identity_abbreviation
            this.inputBirthday = newEdit.birthday === null ? "" : newEdit.birthday
            this.inputCode = newEdit.user_code
            this.inputFirstEmail = newEdit.primary_email
            this.inputSecondEmail = newEdit.secondary_email
            this.inputFirstPhone = newEdit.primary_phone
            this.inputSecondPhone = newEdit.secondary_phone
            this.inputStatusSelect = newEdit.status_id
            //Activamos la casilla de empleado
            this.inputEstadoSelect = newEdit.state_id
            this.inputMunicipioSelect = newEdit.municipality_id
            this.inputParroquiaSelect = newEdit.parish_id
            this.inputDivisionSelect = newEdit.department_id
            this.inputCargoSelect = newEdit.position_id
            this.inputIngreso = newEdit.admission_date === null ? "" : newEdit.admission_date
            this.inputEgreso = newEdit.departure_date === null ? "" : newEdit.departure_date
        },
        //Nombres
        inputFirstname(newString) {
            this.validateString({
                "limitString": this.limitString.NAME,
                "stringToValidate": newString,
                "varInput":'inputFirstname',
                "varError": 'firstnameError',
                "validInput": [true, 'firstnameValid']})
        },
        inputSecondname(newString) {
            this.validateString({
                "limitString": this.limitString.NAME,
                "stringToValidate": newString,
                "varInput":'inputSecondname',
                "varError": 'secondnameError',
                "validInput": [false, '']})
        },
        //Apellidos
        inputLastname(newString) {
            this.validateString({
                "limitString": this.limitString.NAME,
                "stringToValidate": newString,
                "varInput":'inputLastname',
                "varError": 'lastnameError',
                "validInput": [true, 'lastnameValid']})
        },
        inputLastSecondname(newString) {
            this.validateString({
                "limitString": this.limitString.NAME,
                "stringToValidate": newString,
                "varInput":'inputLastSecondname',
                "varError": 'lastsecondnameError',
                "validInput": [false, '']})
        },
        //Fechas
        inputBirthday(newDate) {
            this.validateDate({
                "dateToValidate": newDate,
                "varInput": 'inputBirthday',
                "varError": 'birthdayError',
                "validInput": [true,'birthdayValid']})
        },
        inputIngreso(newDate) {
            this.validateDate({
                "dateToValidate": newDate,
                "varInput": 'inputIngreso',
                "varError": 'ingresoError',
                "validInput": [false,'']})
        },
        inputEgreso(newDate) {
            this.validateDate({
                "dateToValidate": newDate,
                "varInput": 'inputEgreso',
                "varError": 'egresoError',
                "validInput": [false,'']})
        },
        //Emails.
        inputFirstEmail(newEmail) {
            this.validateEmail({
                "emailToValidate": newEmail,
                "varInput": 'inputFirstEmail',
                "varError": 'firstemailError',
                "validInput": [true,'firstemailValid']})
        },
        inputSecondEmail(newEmail) {
            this.validateEmail({
                "emailToValidate": newEmail,
                "varInput": 'inputSecondEmail',
                "varError": 'secondemailError',
                "validInput": [false,'']})
        },
        //Telefonos
        inputFirstPhone(newPhone,oldPhone) {
            this.validatePhone({
                "phoneToValidate": newPhone,
                "phoneOldValue": oldPhone,
                "varInput": 'inputFirstPhone',
                "validInput": [true,'firstphoneValid']})
        },
        inputSecondPhone(newPhone,oldPhone) {
            this.validatePhone({
                "phoneToValidate": newPhone,
                "phoneOldValue": oldPhone,
                "varInput": 'inputSecondPhone',
                "validInput": [false,'']})
        },
        //Watch del documento de identidad
        inputSelect(newSelect, oldSelect) {
            try {
                const valueDTO = newSelect.replace(/\./g, '');
                const verifyFormat = Validate.FormatDocument(valueDTO);
                //Luego de validar transformamos de nuevo la data
                if (!verifyFormat.response) throw verifyFormat.message;
                if (this.inputSelect.length > 2) this.messages.error.documentError = '';

                valueDTO.length > 14
                    ? this.inputSelect = oldSelect
                    : this.inputSelect = verifyFormat.message;

                //Activamos la bandera del documento
                this.submitButton.documentValid = true;
            } catch (errorMessage) {
                //Reestructuramos el formato en un funcion se activo el evento change o no
                this.inputSelect = this.getTargetTypeDocument != ''
                    ? `${this.getTargetTypeDocument}-`
                    : `${this.dataEdit.identity_abbreviation}-`;
                //Desactivamos la bandera
                this.submitButton.documentValid = false;
                //Pasamos el error
                this.messages.error.documentError = Exceptions.CatchWarning(errorMessage);
            }
        },
        //Codigo de usuario
        inputCode(newCode, oldCode) {
            const codeFormat = new RegExp('^([0-9]{0,6})$');
            if (!codeFormat.test(newCode)) this.inputCode = oldCode;

            //Desactivamos la bandera si el input está vacio
            if (this.inputCode.length == 0) this.submitButton.codeValid = false;

            //Activamos la bandera si cumple con la longitud
            if (this.inputCode.length > 0 && this.inputCode.length <= 6) this.submitButton.codeValid = true
        },
        //Activamos de Inputs Watchers
        inputEstadoSelect(getEstado) {
            //Consultamos los municipios
            this.municipality.select = this.municipality.init.filter((municipality) => { return municipality.state_id === getEstado })
        },
        inputMunicipioSelect(getMunicipio) {
            //Consultamos las parroquias
            this.parish.select = this.parish.init.filter((parish) => { return parish.municipality_id === getMunicipio })
        },
        //Watch del submitButton
        submitButton: {
            deep: true,
            handler(checkValid) {
                let contValid = 0; //Contador que define cuantos valores estan validos
                for (const field in checkValid) {
                    if (field.toString() != 'isValid' && checkValid[field] === true) contValid++;
                }

                //Activamos o desactivamos el estilado del boton
                if (contValid == (Object.keys(checkValid).length - 1)) {
                    this.submitButton.isValid = true;
                } else { this.submitButton.isValid = false }
            }
        }
    }
}
