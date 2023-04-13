<template>
    <div :class="formClass.container">
        <form :class="formClass.form">
            <!-- Data Principal -->
            <legend :class="formClass.legend" v-text="isEdit ? messages.titleEdit: messages.titleCreate"></legend>
            <fieldset :class="formClass.fieldset">Data 1</fieldset>
            <!-- Datos de contacto -->
            <legend :class="formClass.legend" v-text="messages.contacto"></legend>
            <fieldset :class="formClass.fieldset">Data 2</fieldset>
            <!-- Datos para el empleado -->
            <legend :class="formClass.legend" v-text="messages.empleado"></legend>
            <fieldset :class="formClass.fieldset">Data 3</fieldset>
            <!-- Submit Button -->
            <button :class="formClass.button" v-text="isEdit ?  messages.buttonEdit : messages.buttonCreate"></button>
        </form>
    </div>
</template>

<script>
export default {
    props:{
        isEdit: Boolean,
        dataEdit: Array,
    },
    data(){
        return{
            formClass:
            {
                container: 'dashboard-form-container',
                form:'',
                legend: '',
                fieldset: '',
                button: ''
            },
            messages:{
                titleCreate: 'Estas creando a un nuevo usuario',
                titleEdit: 'Estas modificando al usuario',
                contacto: 'Datos de contacto',
                empleado: 'Datos para el empleado',
                buttonEdit: 'Actualizar usuario',
                buttonCreate: 'Crear usuario'
            }
        }
    },
    created()
    {
        //Asignamos las clases
        this.formClass.form = this.formClass.container + '-form'
        this.formClass.legend = this.formClass.form + "-legends"
        this.formClass.fieldset = this.formClass.form + "-fieldset"
    }
}
</script>