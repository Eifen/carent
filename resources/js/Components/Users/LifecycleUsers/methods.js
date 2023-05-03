/**
 * Almacena los metodos del componente de usuarios
 */
export const userMethods = {
    methods: {
        //Obtenemos la diferentes datas
        insertDate(dateDTO) { this.inputBirthday = `${dateDTO.year}-${dateDTO.month}-${dateDTO.day}`; },
        insertIngreso(dateDTO) { this.inputIngreso = `${dateDTO.year}-${dateDTO.month}-${dateDTO.day}`; },
        insertEgreso(dateDTO) { this.inputEgreso = `${dateDTO.year}-${dateDTO.month}-${dateDTO.day}`; },
        /**
         * Metodo que habilita el input del documento de identidad
         * @param {*} changeValue Valor obtenido desde el componente DataPrincipal con el cambio del select
         */
        enableInput(changeValue) {
            this.getTargetTypeDocument = changeValue
            this.inputSelect = `${this.getTargetTypeDocument}-`
        },
        /**
         * Metodo que envia la data del formulario  al componente padre
         */
        DTOEmit() {
            //Pasamos los parametros a analizar
            let paramsToEmit =
            {
                "FirstName": this.inputFirstname,
                "SecondName": this.inputSecondname,
                "LastName": this.inputLastname,
                "SecondLastName": this.inputLastSecondname,
                "Cedula": this.inputSelect,
                "Birthday": this.inputBirthday,
                "Code": this.inputCode,
                "FirstEmail": this.inputFirstEmail,
                "SecondEmail": this.inputSecondEmail,
                "FirstPhone": this.inputFirstPhone,
                "SecondPhone": this.inputSecondPhone,
                "IdParish": this.inputParroquiaSelect,
                "IdCargo": this.inputCargoSelect,
                "IdDivision": this.inputDivisionSelect,
                "DateIngreso": this.inputIngreso,
            }

            //Preparamos los parametros de update en caso de actualizar
            let paramsToUpdate =
            {
                "DateEgreso": this.inputEgreso,
                "Status": this.inputStatusSelect
            }

            //Si estamos en edición, unimos los dos objetos
            if (this.isEdit) paramsToEmit = { ...paramsToEmit, ...paramsToUpdate }
            this.$emit('submit-form', paramsToEmit);
        }
    }
}