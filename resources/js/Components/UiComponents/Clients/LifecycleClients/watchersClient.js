import { Validate } from "@/Models/ValidateModel";
import { Exceptions } from "@/Excepciones/Excepciones";

export const clientWatchers = {
    watch: {
        //Select Paises
        inputPaisSelect(newValue) {
            if (newValue != 0) {
                this.submitButton.paisesValid = true;
                //Interamos la data de paises para colocar el código númerico en el campo de telefono
                const selectPais = this.dataSelect.paises.find((pais) => {
                    return pais.country_id == this.inputPaisSelect;
                });
                //En caso de que estemos en crear cliente, añadiremos el codigo del pais
                if (!this.isEdit)
                    this.inputTelefono = `+${selectPais.phone_code}-`;
            } else {
                this.submitButton.paisesValid = false;
            }
        },
        //Razon Social
        inputRazonSocial(newValue) {
            this.validateString({
                limitString: this.LimitString.NAME,
                stringToValidate: newValue,
                varInput: "inputRazonSocial",
                varError: "razonSocialError",
                validInput: [true, "razonSocialValid"],
            });
        },
        //Direccion
        inputDireccion(newValue) {
            this.validateString({
                limitString: this.LimitString.DIR,
                stringToValidate: newValue,
                varInput: "inputDireccion",
                varError: "direccionError",
                validInput: [true, "direccionValid"],
            });
        },
        //Pagina Web
        inputWeb(newValue) {
            this.validateString({
                limitString: this.LimitString.WEB,
                stringToValidate: newValue,
                varInput: "inputWeb",
                varError: "webError",
                validInput: [false, ""],
            });
        },
        //Correo electronico
        inputFirstEmail(newValue) {
            try {
                const validateEmail = Validate.Email(newValue);
                //Correo principal
                if (!validateEmail.response && newValue != "")
                    throw {
                        message: validateEmail.message,
                        errorInput: "firstemailError",
                    };
                //Borramos el error
                if (validateEmail.response || newValue == "")
                    this.messages.error.firstemailError = "";

                //Desactivamos la bandera si el input esta vacio
                if (newValue == "") this.submitButton.firstemailValid = false;

                //Activamos la bandera del correo principal
                if (validateEmail.response)
                    this.submitButton.firstemailValid = true;
            } catch (errorJSON) {
                //Desactivamos la bandera
                if (errorJSON.errorInput == "firstemailError")
                    this.submitButton.firstemailValid = false;
                //Pasamos el error
                this.messages.error[errorJSON.errorInput] =
                    Exceptions.CatchWarning(errorJSON.message);
            }
        },
        //Rif
        inputNit(newValue, oldValue) {
            try {
                const nuevoNit = Validate.Number(newValue);
                //Validacion deL NIT
                if (
                    !nuevoNit.response ||
                    (nuevoNit.response && newValue.length > 11)
                )
                    throw nuevoNit;
                this.inputNit = newValue;
            } catch (error) {
                this.inputNit = oldValue;
            }
        },
        //Rif
        inputRif(newValue) {
            try {
                const nuevoRif = Validate.RifFormat(newValue);
                //Primer nivel de validación
                //message[2] = Es el segundo intervalo del regex en formato ejemplo: V123456789 (123456789)
                if (!nuevoRif.response && newValue.length <= 15)
                    throw nuevoRif.message;
                if (
                    (nuevoRif.response && nuevoRif.message[2].length > 14) ||
                    (!nuevoRif.response && newValue.length > 15)
                )
                    throw "OutRange";
                //Paso las validaciones
                this.submitButton.rifValid = true;
                this.messages.error.rifError = "";
                this.inputRif = newValue.toUpperCase();
            } catch (error) {
                //Capturamos el error
                error == "OutRange"
                    ? (this.messages.error.rifError =
                          Exceptions.CatchWarning(error) +
                          newValue.length +
                          `(${this.LimitString.RIF})`)
                    : (this.messages.error.rifError =
                          Exceptions.CatchWarning(error));
                this.submitButton.rifValid = false;
            }
        },
        //Telefono
        inputTelefono(newValue, oldValue) {
            try {
                const validatePhone = Validate.PhoneClient(newValue);
                let contCode = 0; //Contador para capturar si se ha borrado el código del pais
                //[3] Número de telefono
                if (!validatePhone.response && newValue.length <= 20)
                    throw validatePhone.message;
                if (
                    (validatePhone.response &&
                        validatePhone.message[3].length > 19) ||
                    (!validatePhone.response && newValue.length > 20)
                )
                    throw "OutRange";
                //Verificamos si validatePhone.message[2] que es el código del país coincide con un valor en la data de paises
                if (validatePhone.response) {
                    //Guardamos el codigo del pais
                    contCode = this.dataSelect.paises.find((pais) => {
                        return pais.phone_code === validatePhone.message[2];
                    }).phone_code;
                    if (contCode == 0) this.inputTelefono = oldValue;
                }
                //Pasamos las validaciones
                this.submitButton.telefonoValid = true;
                this.messages.error.telefonoError = "";
                this.inputTelefono = newValue;
            } catch (error) {
                error == "OutRange"
                    ? (this.messages.error.telefonoError =
                          Exceptions.CatchWarning(error) +
                          newValue.length +
                          `(${this.LimitString.TLF})`)
                    : (this.inputTelefono = oldValue);
                this.submitButton.telefonoValid = false;
            }
        },
    },
};
