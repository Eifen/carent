export const projectMethods = 
{
    methods: 
    {
        /**
         * Metodo que controla el estado de la lista
         * @param {*} refTarget Captura un string de la propiedad ref del input
         * @param {*} inputTarget Captura que tipo de campo es para controlarlo con noInput
         * Puede ser cliente, socio, gerente, entre otros
         */
        listControl(refTarget,inputTarget){
            //Controla el estado de la lista
            if(this.$refs[refTarget] !== document.activeElement){
                this.dropDownControl[inputTarget].noInput = false //Lo desactiva si el input pierde el focus
            }else{
                this.dropDownControl[inputTarget].noInput = true //Lo activa si el input tiene focus y no esta vacio
                if(searchCliente.length == 0) this.dropDownControl[inputTarget].noInput = false //Desactiva si input esta vacio
            }
            console.log(this.dropDownControl[inputTarget].noInput,this.$refs)
        }
    },
}