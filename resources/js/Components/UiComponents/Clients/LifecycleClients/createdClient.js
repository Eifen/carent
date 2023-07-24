/**
 * Hook created para formulario de clientes
 * @param {*} self Hereda la data de su padre
 */
export const createdMixin = (self) => {
    //Espacio de creacion de Watchers globales
    self.inputWatchers = [
        {
            propiedades: [
                "inputSectorSelect",
                "inputServicioSelect",
                "inputSocioSelect",
            ],
            watch: () => {
                //Control de banderas
                if (self.inputSectorSelect != 0)
                    self.submitButton.sectorValid = true;
                if (self.inputServicioSelect != 0)
                    self.submitButton.servicioValid = true;
                if (self.inputSocioSelect != 0)
                    self.submitButton.selectSocio = true;

                //Control de desactivar
                if (self.inputSectorSelect == 0)
                    self.submitButton.sectorValid = false;
                if (self.inputServicioSelect == 0)
                    self.submitButton.servicioValid = false;
                if (self.inputSocioSelect == 0)
                    self.submitButton.selectSocio = false;
            },
        },
    ];

    //Axios
    axios
        .post("/clientes/getParamsInits")
        .then((request) => {
            if (request.status !== 200) throw request;
            //Si pasa el control, procedemos a insertarlo
            self.dataSelect.socio = request.data.dataSocio;
            self.dataSelect.servicios = request.data.dataServicios;
            self.dataSelect.sectores = request.data.dataSectores;
            self.dataSelect.paises = request.data.dataPaises;
            self.dataSelect.status = request.data.dataStatus;
            //Revisamos si edit existe
            if (self.$props.isEdit) {
                //Obtenemos el codigo del pais
                const selectPaisCode = self.dataSelect.paises.find((pais) => {
                    return pais.country_id == self.$props.dataEdit.country_id;
                });
                //Formateamos el telefono ([1] numero de telefono)
                const DTOTelefono = self.$props.dataEdit.tax_phone.split(
                    selectPaisCode.phone_code
                );
                //Asignamos la data
                self.inputSocioSelect = self.$props.dataEdit.partner_user_id;
                self.inputSectorSelect = self.$props.dataEdit.sector_id;
                self.inputServicioSelect = self.$props.dataEdit.service_id;
                self.inputPaisSelect = self.$props.dataEdit.country_id;
                self.inputStatusSelect = self.$props.dataEdit.status_id;
                self.inputNit = self.$props.dataEdit.nit;
                self.inputRif = self.$props.dataEdit.rif;
                self.inputTelefono = `+${selectPaisCode.phone_code}-${DTOTelefono[1]}`;
                self.inputRazonSocial = self.$props.dataEdit.bussiness_name;
                self.inputDireccion = self.$props.dataEdit.client_address;
                self.inputFirstEmail = self.$props.dataEdit.tax_email;
                self.inputWeb = self.$props.dataEdit.website;
            }
        })
        .catch((error) => {
            console.error(error);
        });
};
