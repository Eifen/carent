/**
 * Created para el componente usuarios
 * @param {*} self Almacena la data definida en el componente padre
 */
export const createdMixin = (self) =>
{
        //Axios Request
        axios.post('/usuarios/getParamsInit')
        .then(request => {
            if(request.status !== 200) throw request;

            //Si sincroniza con cada una de las listas
            self.typeDocument = request.data.tiposDocumento
            self.stateData = request.data.statesUsuario
            self.divisionData = request.data.divisiones
            self.cargoData = request.data.cargos
            self.statusData = request.data.statusUsuario
            self.municipality.init = request.data.municipalityUsuario
            self.parish.init = request.data.parishUsuario
            //Si la bandera de edit esta activa, pasamos la data almacenada en el cliente
            if(self.isEdit) self.$emit('init-user');
        })
        .catch(error => {
            console.error(error)
        })
}
