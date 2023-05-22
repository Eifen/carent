//Importamos las librerias
import CryptoJS from "crypto-js";
/**
 * Clase enfocada en almacenar Metodos de control de usuarios para el Carent
 */
export class UsersControl
{
    /**
     * Metodo que encripta datos almacenados en un array
     * @param {*} ParametrosEncriptados Objeto donde se ubican parametros encriptados, junto con el key y el iv
     */
    static EncriptarDatos(ParametrosEncriptados){
        const Key = CryptoJS.enc.Hex.parse(ParametrosEncriptados.encryptKey);
        const Iv = CryptoJS.enc.Hex.parse(ParametrosEncriptados.encryptIv);

        //Realizamos el encriptado en formato AES
        const CodeEncript = CryptoJS.AES.encrypt
        (
            ParametrosEncriptados.codigo.toString(),
            Key,
            {
                iv: Iv,
                padding: CryptoJS.pad.ZeroPadding
            }
        );

        const PasswordEncript = CryptoJS.AES.encrypt
        (
            ParametrosEncriptados.password,
            Key,
            {
                iv: Iv,
                padding: CryptoJS.pad.ZeroPadding
            }
        );

        const LoginEncriptado = { Codigo: CodeEncript.toString(), Clave: PasswordEncript.toString() }

        //Retornamos el objeto
        return LoginEncriptado;
    }
}
