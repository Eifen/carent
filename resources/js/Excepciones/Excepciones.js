export class Exceptions
{
    /**
     * Metodo estatico que captura el nombre de la excepción.
     * @param {*} $StringException Tipo de error. String
     * @returns Object o string con el tipo de error
     */
    static CatchWarning($StringException)
    {
        switch ($StringException) {
            //Login Exception
            case 'NoCodigo':
                return {codigoError: "Falta colocar el código de usuario", passwordError:""};

            case 'NoPassword':
                return {codigoError: "", passwordError:"Falta colocar la contraseña"};

            case 'EmptyData':
                return {codigoError: "Falta colocar el código de usuario", passwordError:"Falta colocar la contraseña"};

            //Date Exception
            case 'NoDateFormat':
                return "El formato de fecha no es el correcto";

            case 'InvalidDate':
                return "La fecha es incorrecta";

            //String Exception
            case 'OutRange':
                return "Se ha excedido el rango de caracteres. Actual (Máximo) = ";

            case 'EmptyString':
                return "Este valor no puede estar vacio";

            //Number Exception
            case 'IsNotNumber':
                return "Ingrese un número válido";

            //Format Exception
            case 'NoDocumentFormat':
                return "El documento de identidad no posee el formato correcto";

            case 'NoEmailFormat':
                return "El correo no posee el formato correo. Ejemplo: usuarios@empresa.dominio";
            
            //Init Rif
            case 'NoInitRif':
                return "El rif debe iniciar con: V, E, P, G, J o C"
        }
    }
}
