import { Validate } from "../../Models/ValidateModel";
import { Exceptions } from "../../Excepciones/Excepciones";

/**
 * Mixin para el watcher de BillingControl.js
 */
export const billingWatch = {
    watch: {
        //Tipo de factura
        inputConcept(newConcept) {
            this.emptyFields();
            if (this.isEdit) this.enableEdit();
            try {
                //No se ha seleccionado nada. Desactivamos el boton
                if (newConcept == 0) throw "NoSelect";
                //Caso contrario
                else this.validate.conceptValid = true;
                //Condiciones para el tipo de selector
                switch (newConcept) {
                    case 4:
                        this.validate.billingValid = false;
                        this.validate.dateValid = false;
                        this.validate.valueValid = true;
                        this.validate.nullBillValid = false;
                        this.validate.nullControlValid = false;
                        break;
                    case 5:
                        this.validate.valueValid = false;
                        this.validate.billingValid = true;
                        this.validate.dateValid = true;
                        this.validate.nullBillValid = true;
                        this.validate.nullControlValid = true;
                        break;
                    default:
                        this.validate.billingValid = false;
                        this.validate.dateValid = false;
                        this.validate.valueValid = false;
                        this.validate.nullBillValid = true;
                        this.validate.nullControlValid = true;
                        break;
                }
            } catch (error) {
                this.validate.conceptValid = false;
            }
        },
        //Numero de factura
        inputBilling(newBilling) {
            try {
                const strinValidate = Validate.String(
                    newBilling,
                    this.limitString.number
                );
                //Si supera el numero del limite muestra un error
                if (!strinValidate.response) throw strinValidate.message;
                //Caso contrario limpia el error
                if (this.inputConcept == 5) this.validate.billingValid = true;
                if (strinValidate.response && newBilling.length != 0) {
                    this.validate.billingValid = true;
                    //Limpiamos el error
                    this.errorMessage.billingError = "";
                }
                //Si el campo esta vacio, desactivamos la bandera
                if (newBilling.length == 0 && this.inputConcept != 5) {
                    this.validate.billingValid = false;
                    //Limpiamos el error
                    this.errorMessage.billingError =
                        "El campo no puede estar vacio";
                }
            } catch (error) {
                if (this.inputConcept != 5) this.validate.billingValid = false;
                //Mostramos el error
                this.errorMessage.billingError = `${
                    Exceptions.CatchWarning(error) + newBilling.length
                }(${this.limitString.number})`;
            }
        },
        //Fecha de emision
        inputDate(newDate) {
            try {
                const dateValidate = Validate.Date(newDate);
                if (!dateValidate.response && newDate.length >= 10)
                    throw dateValidate.message;
                //Pasa las validaciones
                if (newDate.length == 0 || newDate.length <= 10)
                    this.errorMessage.dateError = "";
                if (this.inputConcept == 5) this.validate.dateValid = true;
                if (dateValidate.response) {
                    this.validate.dateValid = true;
                    //Desactivamos los errores
                    this.errorMessage.dateError = "";
                }
            } catch (error) {
                if (this.inputConcept != 5) this.validate.dateValid = false;
                //Mostramos el error
                this.errorMessage.dateError = Exceptions.CatchWarning(error);
            }
        },
        inputValue(newValue) {
            try {
                const formatNumber = newValue
                    .replace(/\./g, "")
                    .replace(",", ".");
                const numberValid = Validate.Number(formatNumber);
                //Verificamos si es un numero mayor a 0
                if (!numberValid.response) throw numberValid.message;
                if (newValue <= 0) throw "IsNotNumber";
                //Si cumple con la condicion de numero decimal cambiamos el formato y activamos
                if (this.inputConcept == 4) this.validate.valueValid = true;
                if (newValue.length == 0) this.errorMessage.valueError = "";
                //el control del boton
                if (numberValid.response) {
                    this.inputValue =
                        Number(formatNumber).toLocaleString("de-DE");
                    this.validate.valueValid = true;
                    this.errorMessage.valueError = "";
                }
            } catch (errorMessage) {
                if (this.inputConcept != 4) this.validate.valueValid = false;
                //Mostramos los errores
                if (newValue.length != 0)
                    this.errorMessage.valueError =
                        Exceptions.CatchWarning(errorMessage);
            }
        },
        inputDescription(newDescription) {
            try {
                const strinValidate = Validate.String(
                    newDescription,
                    this.limitString.comments
                );
                //Si supera el numero del limite muestra un error
                if (!strinValidate.response) throw strinValidate.message;
                //Caso contrario limpia el error
                if (strinValidate.response && newDescription.length != 0)
                    this.errorMessage.descriptionError = "";
            } catch (error) {
                //Mostramos el error
                this.errorMessage.descriptionError = `${
                    Exceptions.CatchWarning(error) + newDescription.length
                }(${this.limitString.comments})`;
            }
        },
        inputControl(newControl) {
            try {
                const strinValidate = Validate.String(
                    newControl,
                    this.limitString.number
                );
                //Si supera el numero del limite muestra un error
                if (!strinValidate.response) throw strinValidate.message;
                //Caso contrario limpia el error
                if (strinValidate.response && newControl.length != 0)
                    this.errorMessage.controlError = "";
            } catch (error) {
                //Mostramos el error
                this.errorMessage.controlError = `${
                    Exceptions.CatchWarning(error) + newControl.length
                }(${this.limitString.number})`;
            }
        },
        inputPayment(newPayment) {
            try {
                const dateValidate = Validate.Date(newPayment);
                if (!dateValidate.response && newPayment.length >= 10)
                    throw dateValidate.message;
                //Pasa las validaciones
                if (newPayment.length == 0 || newPayment.length <= 10)
                    this.errorMessage.paymentError = "";
                if (dateValidate.response) this.errorMessage.paymentError = "";
            } catch (error) {
                //Mostramos el error
                this.errorMessage.paymentError = Exceptions.CatchWarning(error);
            }
        },
        inputObservation(newObservation) {
            try {
                const strinValidate = Validate.String(
                    newObservation,
                    this.limitString.comments
                );
                //Si supera el numero del limite muestra un error
                if (!strinValidate.response) throw strinValidate.message;
                //Caso contrario limpia el error
                if (strinValidate.response && newObservation.length != 0)
                    this.errorMessage.observationError = "";
            } catch (error) {
                //Mostramos el error
                this.errorMessage.observationError = `${
                    Exceptions.CatchWarning(error) + newObservation.length
                }(${this.limitString.comments})`;
            }
        },

        validate: {
            handler(newValidate) {
                let countValid = 0;
                //Contamos los valores que esten en true
                for (const field in newValidate) {
                    if (
                        field.toString() != "isValid" &&
                        newValidate[field] == true
                    )
                        countValid++;
                }

                //Activamos el isValid o no
                if (countValid == Object.keys(newValidate).length - 1)
                    this.validate.isValid = true;
                else this.validate.isValid = false;
            },
            deep: true,
        },
    },
};
