/**
 * Hook created para formulario de clientes
 * @param {*} self Hereda la data de su padre
 */
export const createdMixin = (self) => {
    //Espacio de creacion de Watchers globales
    self.inputWatchers =
    [{
        propiedades: ['inputSectorSelect','inputServicioSelect','inputSocioSelect'],
        watch: () =>
        {
            //Control de banderas
            if(self.inputSectorSelect != 0) self.submitButton.sectorValid = true;
            if(self.inputServicioSelect != 0) self.submitButton.servicioValid = true;
            if(self.inputSocioSelect != 0) self.submitButton.selectSocio = true;

            //Control de desactivar
            if(self.inputSectorSelect == 0) self.submitButton.sectorValid = false;
            if(self.inputServicioSelect == 0) self.submitButton.servicioValid = false;
            if(self.inputSocioSelect == 0) self.submitButton.selectSocio = false;
        }
    }]

    //Axios
    axios.post('/clientes/getParamsInits')
    .then(request => {
        if(request.status !== 200) throw request;
        //Si pasa el control, procedemos a insertarlo
        self.dataSelect.socio = request.data.dataSocio
        self.dataSelect.servicios = request.data.dataServicios
        self.dataSelect.sectores = request.data.dataSectores
        self.dataSelect.paises = request.data.dataPaises
        self.dataSelect.status = request.data.dataStatus
        //Revisamos si edit existe
        if(self.isEdit) self.$emit('init-client');
    })
    .catch(error => {
        console.error(error);
    });
}
