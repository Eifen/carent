/**
 * Created para el componente usuarios
 * @param {*} self Almacena la data definida en el componente padre
 */
export const createdMixin = (self) => {
    //Axios Request
    axios
        .post("/usuarios/getParamsInit")
        .then((request) => {
            if (request.status !== 200) throw request;

            //Si sincroniza con cada una de las listas
            self.typeDocument = request.data.tiposDocumento;
            self.stateData = request.data.statesUsuario;
            self.divisionData = request.data.divisiones;
            self.cargoData = request.data.cargos;
            self.statusData = request.data.statusUsuario;
            self.municipality.init = request.data.municipalityUsuario;
            self.parish.init = request.data.parishUsuario;

            //Si estamos en la pantalla del edit, llenamos los campos
            if (self.$props.isEdit) {
                self["inputFirstname"] = self.$props.dataEdit.first_name;
                self["inputSecondname"] = self.$props.dataEdit.second_name;
                self["inputLastname"] = self.$props.dataEdit.first_surname;
                self["inputLastSecondname"] =
                    self.$props.dataEdit.second_surname;
                self["inputSelect"] =
                    self.$props.dataEdit.identity_abbreviation +
                    "-" +
                    self.$props.dataEdit.identity_number;
                self["inputDocumentoSelect"] =
                    self.$props.dataEdit.identity_abbreviation;
                self["inputBirthday"] =
                    self.$props.dataEdit.birthday === null
                        ? ""
                        : self.$props.dataEdit.birthday;
                self["inputCode"] = self.$props.dataEdit.user_code;
                self["inputFirstEmail"] = self.$props.dataEdit.primary_email;
                self["inputSecondEmail"] = self.$props.dataEdit.secondary_email;
                self["inputFirstPhone"] = self.$props.dataEdit.primary_phone;
                self["inputSecondPhone"] = self.$props.dataEdit.secondary_phone;
                self["inputStatusSelect"] = self.$props.dataEdit.status_id;
                //Activamos la casilla de empleado
                self["inputEstadoSelect"] = self.$props.dataEdit.state_id;
                self["inputMunicipioSelect"] =
                    self.$props.dataEdit.municipality_id;
                self["inputParroquiaSelect"] = self.$props.dataEdit.parish_id;
                self["inputDivisionSelect"] =
                    self.$props.dataEdit.department_id;
                self["inputCargoSelect"] = self.$props.dataEdit.position_id;
                self["inputIngreso"] =
                    self.$props.dataEdit.admission_date === null
                        ? ""
                        : self.$props.dataEdit.admission_date;
                self["inputEgreso"] =
                    self.$props.dataEdit.departure_date === null
                        ? ""
                        : self.$props.dataEdit.departure_date;
            }
        })
        .catch((error) => {
            console.error(error);
        });
};
