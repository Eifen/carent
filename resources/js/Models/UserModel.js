//Importamos las librerias
import axios from "axios";
import CryptoJS from "crypto-js";
import { AES } from "crypto-js";
/**
 * Clase enfocada en almacenar Metodos de control de usuarios para el Carent
 */
export class UsersControl 
{
    /**
     * Metodo enfocado en iniciar sesión
     * @param {*} $Password Almacena la password del input
     * @param {*} $User Almacena el Usuario del inpu
     */
    login($Password, $User){
        //Encriptamos la data antes de llamarla por Axios
        
    }

    /**
     * Metodo que encripta datos almacenados en un array
     * @param {*} $ParametrosEncriptados Array donde se ubican parametros encriptados
     * @param {*} $KeyVar Variable en string en base al texto clave que se quiera encriptar
     * @param {*} $IvVar Variable en string en base al texto que se quiera usar como prefix en la encriptación
     */
    EncriptarDatos($ParametrosEncriptados, $KeyVar, $IvVar){

    }
}