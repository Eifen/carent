//Importamos las librerias
import axios from "axios";
import CryptoJS from "crypto-js";
import AES from "crypto-js/aes";
/**
 * Clase enfocada en almacenar Metodos de control de usuarios para el Carent
 */
export class UsersControl 
{
    /**
     * Metodo enfocado en iniciar sesión
     * @param {*} $Password Almacena la password del input
     * @param {*} $User Almacena el Usuario del input
     * @param {*} $KeyEncriptada Contexto del encript
     * @param {*} $IvEncriptada Vector del encript
     */
    login($Password, $User, $KeyEncriptada, $IvEncriptada){
        const ObjectToEncript = 
        {
            "codigo": $User,
            "password": $Password,
            "encryptKey": $KeyEncriptada,
            "encryptIv": $IvEncriptada
        }
        //Encriptamos la data antes de llamarla por Axios
        const ParamsLogin = this.EncriptarDatos(ObjectToEncript);

        console.log(ParamsLogin);
        axios.post('/login',ParamsLogin)
        .then(function (response)
        {
            console.log(response);
        })
        .catch( error => { alert("Pene"); })
    }

    /**
     * Metodo que encripta datos almacenados en un array
     * @param {*} ParametrosEncriptados Objeto donde se ubican parametros encriptados, junto con el key y el iv
     */
    EncriptarDatos(ParametrosEncriptados){
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