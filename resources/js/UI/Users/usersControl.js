
const createUser = createApp({
    data(){
        return{
            isMounted: false,
            isMountedEdit: false, //Controla la carga del formulario de update
            encryptKey: '', //Llave del encrypt
            encryptIv: '', //Vector del encrypt
            isCreateClick: false, //Controla el boton de crear usuario
            isEditClick: false, //Controla el boton de edit usuario
            updateModel: {},
            paramDTOUser:
            {
                "FirstName": '',
                "SecondName": '',
                "LastName": '',
                "SecondLastName": '',
                "Cedula": '',
                "Birthday": '',
                "Code": '',
                "IdParish": 0,
                "IdCargo": 0,
                "IdDivision": 0,
                "DateIngreso": ''
            }, //Objeto que prepara la data para crear el usuario
            paramDTOEdit:
            {
                "IdUser": 0,
                "IdStatus": 0,
                "DateEgreso": ''
            },//Objeto que almacena los campos del edit
            paramDTONewContact:
            {
                "FirstEmail": '',
                "SecondEmail": '',
                "FirstPhone": '',
                "SecondPhone": ''
            }, //Objeto que prepara la data para insertar el contacto del nuevo Usuario
            paramDTONewDocument:
            {
                "TipoCedula": '',
                "Cedula": ''
            } //Objeto que prepara la data para insertar los datos del documento
        }
    },
    mounted(){
        setTimeout(() => {
            this.isMounted = true;
        }, 300);
    },
    methods:{
        /**
         * Metodo que redirecciona a la pantalla anterior
         */
        redirectView(){ window.location.href = "/usuarios" },
        /**
         * Metodo que registra un nuevo usuario
         * @param {*} Data Almacena la data inicial del usuario
         */
        newUser(Data){

            this.isCreateClick = true;
            this.validateNivel2(Data);
            //Solicitamos al controlador
            axios.post('/usuarios/create/newUser',{
                "user": JSON.parse(JSON.stringify(this.paramDTOUser)),
                "contact": JSON.parse(JSON.stringify(this.paramDTONewContact)),
                "document": JSON.parse(JSON.stringify(this.paramDTONewDocument)),
                "isEdit": false})
            .then(request => {
                if(request.status === 200 && !request.data.response) throw request.data.message;
                toast.success(request.data.message, {
                    position: toast.POSITION.TOP_LEFT,
                    autoClose: false
                });

                setTimeout(() => {
                    window.location.href = "/usuarios";
                }, AXIOSINTERVAL + 200);

            }).catch(error => {
                toast.error(error, {
                    position: toast.POSITION.TOP_LEFT,
                    autoClose:NOTIFYINTERVAL
                });

                this.isCreateClick = false;
            })
        },
        /**
         * Metodo que actualiza el data model para actualizar usuarios
         * @param {*} dataUser Datos del usuario a actualizar
         */
        prepareUpdate(dataUser){
            this.updateModel = dataUser;
            //Una vez almacenada, eliminamos su valor en la sessión para que sea de unico uso
            axios.put('deleteUpdateData')
            .then(request => {})
            .catch(error => {
                console.error(error);
            })
        },
        /**
         * Metodo que actualiza un usuario
         * @param {*} Data Almacena los datos a actualizar
         */
        updateUser(Data)
        {
            this.isEditClick = true;
            this.validateNivel2(Data);

            //Validaciones unicas para edit
            Data.Status == 0 ? this.paramDTOEdit.IdStatus = null : this.paramDTOEdit.IdStatus = Data.Status
            !Validate.Date(Data.DateEgreso).response
            ? this.paramDTOEdit.DateEgreso = ''
            : this.paramDTOEdit.DateEgreso = Data.DateEgreso;

            //Pasamos el Id del usuario
            this.paramDTOEdit.IdUser = this.updateModel.user_id;

            //Unimos el array user en edit
            this.paramDTOEdit = { ...this.paramDTOEdit, ...this.paramDTOUser}

            //Conexión AXIOS Update User
            axios.post('/usuarios/update/updateUser',{
                "user": JSON.parse(JSON.stringify(this.paramDTOEdit)),
                "contact": JSON.parse(JSON.stringify(this.paramDTONewContact)),
                "document": JSON.parse(JSON.stringify(this.paramDTONewDocument)),
                "isEdit": true})
            .then(request =>
                {
                    if(request.status === 200 && !request.data.response) throw request.data.message;
                    toast.success(request.data.message, {
                        position: toast.POSITION.TOP_LEFT,
                        autoClose: false
                    });

                    setTimeout(() => {
                        window.location.href = "/usuarios";
                    }, AXIOSINTERVAL + 200);
                })
            .catch(error =>
                {
                    toast.error(error, {
                        position: toast.POSITION.TOP_LEFT,
                        autoClose:NOTIFYINTERVAL
                    });

                    this.isEditClick = false;
                })
        },
        /**
         * Reformatea el telefono quitando los caracteres "()" y "-"
         * @param {*} phoneValue Captura el valor actual del telefono
         * @return String que contiene el nuevo formato del telefono
         */
        decryptPhone(phoneValue){ return phoneValue.replace('(','').replace(')','').replace(/-/g,''); },
        /**
         * Metodo que realiza un segundo nivel de validaciones para los datos no obligatorios
         * Se basa en descomponer cada campo y verificar su validez.
         * @param {*} DataToReview Objeto de datos a analizar
         */
        validateNivel2(DataToReview)
        {
            const DTOCedula = DataToReview.Cedula.split('-'); //[0] = Tipo, [1] = Cedula
            const DTONumeroSecundario = DataToReview.SecondPhone;
            const DTOEmailSecundario = DataToReview.SecondEmail;
            const DTOSecondName = DataToReview.SecondName;
            const DTOSecondLastName = DataToReview.SecondLastName;
            const DTOFechaIngreso = DataToReview.DateIngreso;
            const DTOCode = UsersControl.EncriptarDatos(
                {
                    "encryptKey": this.encryptKey,
                    "encryptIv": this.encryptIv,
                    "codigo": DataToReview.Code,
                    "password": '000000'
                })
            //Segundo nivel de validaciones
            DataToReview.IdParish == 0 ? this.paramDTOUser.IdParish = 0 : this.paramDTOUser.IdParish = DataToReview.IdParish
            DataToReview.IdCargo == 0 ? this.paramDTOUser.IdCargo = 0 : this.paramDTOUser.IdCargo = DataToReview.IdCargo
            DataToReview.IdDivision == 0 ? this.paramDTOUser.IdDivision = 0 : this.paramDTOUser.IdDivision = DataToReview.IdDivision

            //Descomponer cedula
            this.paramDTOUser.Cedula = DTOCedula[1].replace(/\./g,'');
            this.paramDTONewDocument.Cedula = this.paramDTOUser.Cedula;
            this.paramDTONewDocument.TipoCedula = DTOCedula[0];

            //Descomponer numero
            !Validate.Phone(DTONumeroSecundario).response
            ? this.paramDTONewContact.SecondPhone = ''
            : this.paramDTONewContact.SecondPhone = this.decryptPhone(DTONumeroSecundario);

            this.paramDTONewContact.FirstPhone = this.decryptPhone(DataToReview.FirstPhone);

            //Descomponer Email
            !Validate.Email(DTOEmailSecundario).response
            ? this.paramDTONewContact.SecondEmail = ''
            : this.paramDTONewContact.SecondEmail = DTOEmailSecundario;

            this.paramDTONewContact.FirstEmail = DataToReview.FirstEmail;

            //Descomponer Nombres
            !Validate.String(DTOSecondName,20).response
            ? this.paramDTOUser.SecondName = ''
            : this.paramDTOUser.SecondName = DTOSecondName;

            !Validate.String(DTOSecondLastName,20).response
            ? this.paramDTOUser.SecondLastName = ''
            : this.paramDTOUser.SecondLastName = DTOSecondLastName;

            this.paramDTOUser.FirstName = DataToReview.FirstName;
            this.paramDTOUser.LastName = DataToReview.LastName;

            //Descomponer Fechas
            !Validate.Date(DTOFechaIngreso).response
            ? this.paramDTOUser.DateIngreso = ''
            : this.paramDTOUser.DateIngreso = DataToReview.DateIngreso;

            this.paramDTOUser.Birthday = DataToReview.Birthday

            //Descomponer Codigo
            this.paramDTOUser.Code = `${DTOCode.Codigo}`
        },
        /**
         * Recibe la data de encriptacion de la base de datos y lo asigna  a dos variables en Data
         * @param {*} encryptKey Llave del encrypt
         * @param {*} encryptIv Vector del encrypt
         */
        getEncrypt(encryptKey, encryptIv)
        {
            this.encryptKey = encryptKey;
            this.encryptIv = encryptIv;
        }
    },
    components: { FontAwesome, Loading, FormUsers, Calendar }
});

if(document.getElementById('create-users') !== null || document.getElementById('update-user') !== null)
{
    if (document.getElementById('create-users') !== null) createUser.mount('#create-users');
    if (document.getElementById('update-user') !== null) createUser.mount('#update-user');
    window.location.hash = "#01";
};
