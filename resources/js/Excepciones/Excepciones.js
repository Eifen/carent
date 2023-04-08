export class Exceptions
{
    /**
     * Metodo estatico que captura el nombre de la excepción.
     */
    static CatchWarning($StringException)
    {
        switch ($StringException) {
            case 'NoCodigo':
                return {codigoError: "Falta colocar el código de usuario", passwordError:""};

            case 'NoPassword':
                return {codigoError: "", passwordError:"Falta colocar la contraseña"};

            case 'EmptyData':
                return {codigoError: "Falta colocar el código de usuario", passwordError:"Falta colocar la contraseña"};
        }
    }
}
