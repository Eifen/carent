import { Validate } from "@/Models/ValidateModel";
import { Exceptions } from "@/Excepciones/Excepciones";

export const evaluationWatchers = {
    watch: {
        //Select Paises
        inputTipoSelect(newValue) {
            if (newValue != 0) {
                this.submitButton.selectTipo = true;
                //Interamos la data de paises para colocar el código númerico en el campo de telefono
                // const selectPais = this.dataSelect.paises.find((pais) => {
                //     return pais.country_id == this.inputPeriodo;
                // });
            }
        },
    },
};
