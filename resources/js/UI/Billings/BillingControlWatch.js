import { Validate } from "../../Models/ValidateModel";
import { Exceptions } from "../../Excepciones/Excepciones";

/**
 * Mixin para el watcher de BillingControl.js
 */
export const billingWatch = {
    watch: {
        //Tipo de factura
        inputConcept(newConcept) {
            try {
                //No se ha seleccionado nada. Desactivamos el boton
                if (newConcept == 0) throw "NoSelect";
                //Caso contrario
                else this.validate.conceptValid != true;
            } catch (error) {
                this.validate.conceptValid = false;
            }
        },

        validate: {
            handler(newValidate) {
                console.log(newValidate);
            },
            deep: true,
        },
    },
};
