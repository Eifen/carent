/**
 * Objeto que establece la validacion de datos
 */
export class Validate
{
    /**
     * Metodo que valida el formato del string
     * @param {*} stringData  Almacena el string
     * @param {*} maxLength Define en base a cuanta longitud se quiere tomar
     */
    static String(stringData,maxLength)
    {
        //Mayor a 20, falla la validación
        if(stringData.length <= maxLength) return {"response":true,"message":"Success"};
        if(stringData.length == 0 || stringData == null) return {"response":false,"message":"EmptyString"}
        return {"response":false,"message":"OutRange"};
    }

    /**
     * Verifica si la fecha tiene formato YYYY-mm-dd y si tiene una fecha bisiesta correcta
     * @param {*} dateData Almacena el string de la fecha
     * @returns Object {response:boolean, message: string}
     */
    static Date(dateData)
    {
        //Formato de fecha
        const dateRegex = new RegExp("^([0-9][0-9][0-9]{2})-([0][0-9]|[1][0-2])-([0-2][0-9]|[3][0-1])$")
        if(dateRegex.test(dateData))
        {
            const splitDate = dateData.split('-'); //[0] Year, [1] Month, [2] Day
            //Ultimo día del mes ingresado
            const lastDay = new Date(splitDate[0],splitDate[1],0);

            //Validaciones
            switch(true)
            {
                //Si supera el ultimo día
                case splitDate[2] > lastDay.getDate():
                    return {"response":false,"message":"InvalidDate"};
                //Pasa las validaciones
                default:
                    return {"response":true,"message":"SuccessDate"};
            }
        }
        //No cumple con el formato
        return {"response":false,"message":"NoDateFormat"};
    }

    /**
     * Verifica si el documento de identidad posee el formato correcto
     * @param {*} documentData Captura el documento ingresado
     * @return Object {response: boolean, message: string (Error o Data)}
     */
    static FormatDocument(documentData)
    {
        let splitDocument = documentData.split('-');
        let documentDTO = '';
        const documentFormat  = new RegExp('^(V-|E-)([0-9]{1,3}(\.[0-9]{3})*|[0-9]{1-3})$');
        const numberDocument = splitDocument[1];
        const verifyNumber = this.Number(splitDocument[1]);

        //Separamos el documento para verificar el dato del segundo valor del array (splitDocument[1])
        if(splitDocument.length != 2) return {"response":false,"message":"NoDocumentFormat"};

        if(verifyNumber.response)
        {
            //Si es un número, volvemos a unir el array luego de formatear el numero
            splitDocument[1] = Number(numberDocument).toLocaleString('de-DE');
            documentDTO = splitDocument.join('-');
            //Procedemos a verificar si cumple con el formato
            if(documentFormat.test(documentDTO)) return {"response":true,"message":documentDTO};
            return {"response":false,"message":"NoDocumentFormat"}
        }

        return {"response":false,"message":verifyNumber.message}
    }

    /**
     * Verifica si el digito es un número
     * @param {*} numberData
     * @return Object {response: boolean, message: string}
     */
    static Number(numberData)
    {
        const numberFormat = new RegExp('^([0-9])+$');
        if(numberFormat.test(numberData)) return {"response":true,"message":"Success"};
        return {"response":false, "message":"IsNotNumber"}
    }

    /**
     * Verifica si el correo tiene el formato correcto
     * @param {*} emailData Captura el input del correo
     * @returns Object {response:boolean, message: string}
     */
    static Email(emailData)
    {
        const emailFormat = new RegExp('^\\w+[\\w-\.]*\\@\\w+([-\.]\\w+)*\\.\\w+([\-\.]\\w+)*$');
        if(emailFormat.test(emailData.toString())) return {"response":true,"message":"Success"};
        return {"response":false, "message":"NoEmailFormat"}
    }

    /**
     * Verifica si el numero de telefono tiene el formato correcto
     * @param {*} phoneData Captura el input del telefono
     * @returns Object {response:boolean, message: string (Error o FormatPhone)}
     */
    static Phone(phoneData)
    {
        const phoneFormat = new RegExp('^\(([0-9]{4})\)-([0-9]{3})-([0-9]{4})$');
        let isCompleteFormat = false //Condicion para el PhoneFormat.
        const numberFormat = new RegExp('^([0-9]{0,11})$');
        let newFormat = ''

        //Verificamos si es un número
        if(!numberFormat.test(phoneData)) return {"response":false,"message": "Failure"}

        //Revisamos que longitud tiene el número y asignamos el nuevo formato
        switch(true)
        {
            case phoneData.length > 4 && phoneData.length <= 7 :
                //(1111)-111
                newFormat = `(${phoneData.substring(0,4)})-${phoneData.substring(4,7)}`
                break;
            case phoneData.length > 7 && phoneData.length <= 11:
                //(1111)-111-1111
                newFormat = `(${phoneData.substring(0,4)})-${phoneData.substring(4,7)}-${phoneData.substring(7,11)}`
                break;
            case phoneData.length == 11:
                isCompleteFormat = true;
                break;
            default:
                //1111
                newFormat = phoneData
                break;
        }

        if(phoneFormat.test(phoneData) && isCompleteFormat) return {"response":true,"message":newFormat};
        if(!isCompleteFormat) return {"response":true,"message":newFormat}

        return {"response":false,"message":"Failure"}
    }
    static RifFormat(rifData)
    {
        const rifDTO = new RegExp('^(V|E|J|P|G|C)$');
        //Revisamos si coincide
        if (rifDTO.test(rifData)) return {"reponse":true,"message":"Success"};
        //Si no coincide
        return {"response":false,"message":"NoInitRif"}
    }
}
