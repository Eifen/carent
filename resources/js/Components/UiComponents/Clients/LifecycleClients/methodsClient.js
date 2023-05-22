export const clientMethods =
{
    methods:
    {
        /**
         * Metodo que envia la data del formulario  al componente padre
         */
        clientEmit() {
            //Pasamos los parametros a analizar
            let paramsToEmit =
            {
                "IdSocio": this.inputSocioSelect,
                "IdSector": this.inputSectorSelect,
                "IdServicio": this.inputServicioSelect,
                "IdPais": this.inputPaisSelect,
                "Nit": this.inputNit.toString(),
                "Rif": this.inputRif.toString(),
                "Telefono": this.inputTelefono.replace('-',""),
                "RazonSocial": this.inputRazonSocial.toString(),
                "Direccion":this.inputDireccion.toString(),
                "EmailFiscal": this.inputFirstEmail.toString(),
                "PaginaWeb": this.inputWeb.toString(),
            }

            //Preparamos los parametros de update en caso de actualizar
            let paramsToUpdate =
            {
                "Status": this.inputStatusSelect
            }

            //Si estamos en edición, unimos los dos objetos
            if (this.isEdit) paramsToEmit = { ...paramsToEmit, ...paramsToUpdate }
            this.$emit('submit-form', paramsToEmit);
        }
    }
}
