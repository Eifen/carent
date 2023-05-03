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
            this.inputFirstname = newEdit.Primer_nombre
            this.inputSecondname = newEdit.Segundo_nombre
            this.inputLastname = newEdit.Primer_Apellido
            this.inputLastSecondname = newEdit.Segundo_apellido
            this.inputSelect = newEdit.AbreviaturaTipo + "-" + newEdit.Cedula
            this.inputDocumentoSelect = newEdit.AbreviaturaTipo
            this.inputBirthday = newEdit.Fecha_nacimiento
            this.inputCode = newEdit.Codigo
            this.inputFirstEmail = newEdit.Correo_principal
            this.inputSecondEmail = newEdit.Correo_secundario
            this.inputFirstPhone = newEdit.Telefono_principal
            this.inputSecondPhone = newEdit.Telefono_secundario
            this.inputStatusSelect = newEdit.StatusId
            //Activamos la casilla de empleado
            this.inputEstadoSelect = newEdit.EstadoId
            this.inputMunicipioSelect = newEdit.MunicipioId
            this.inputParroquiaSelect = newEdit.ParroquiaId
            this.inputDivisionSelect = newEdit.DivisionId
            this.inputCargoSelect = newEdit.CargoId
            this.inputIngreso = newEdit.Fecha_ingreso
        },

        //Watch del documento de identidad
        inputSelect(newSelect) {
            try {
                const valueDTO = newSelect.replace(/,/g, '');
                const verifyFormat = Validate.FormatDocument(valueDTO);
                //Luego de validar transformamos de nuevo la data
                if (!verifyFormat.response) throw verifyFormat.message;
                if (this.inputSelect.length > 2) this.messages.form.documentError = '';

                this.inputSelect = verifyFormat.message
                //Activamos la bandera del documento
                this.submitButton.documentValid = true;
            } catch (errorMessage) {
                //Reestructuramos el formato en un funcion se activo el evento change o no
                this.inputSelect = this.getTargetTypeDocument != ''
                    ? `${this.getTargetTypeDocument}-`
                    : `${this.dataEdit.AbreviaturaTipo}-`;
                //Desactivamos la bandera
                this.submitButton.documentValid = false;
                //Pasamos el error
                this.messages.form.documentError = Exceptions.CatchWarning(errorMessage);
            }
        },
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
            const paramsToPost = { "IdState": getEstado }
            //Consultamos los municipios
            axios.post('/usuarios/getMunicipality', paramsToPost)
                .then(request => {
                    if (request.status !== 200) throw request;

                    //Sincronizamos
                    this.municipalityData = request.data
                }).catch(error => {
                    console.error(error);
                })
        },
        inputMunicipioSelect(getMunicipio) {
            const paramsToPost = { "IdMunicipio": getMunicipio }
            //Consultamos las parroquias
            axios.post('/usuarios/getParish', paramsToPost)
                .then(request => {
                    if (request.status !== 200) throw request;

                    //Sincronizamos
                    this.parishData = request.data;
                }).catch(error => {
                    console.error(error);
                })
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