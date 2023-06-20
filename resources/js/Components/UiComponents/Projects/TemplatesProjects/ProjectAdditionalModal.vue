<template>
    <span>
        <font-awesome string-icon="fa-solid fa-plus"
        class="aLink"
        data-bs-toggle="modal"
        :data-bs-target="'#' + idModal"></font-awesome>
    </span>
    <!-- Modal -->
    <div class="modal" tabindex="-1" :id="idModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ modalTitle }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Modal body text goes here.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary">Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import FontAwesome from '@/Components/FontAwesome/FontAwesome.vue';

export default {
    props:{
        modalTitle: String, //Captura el titulo del modal,
        modalData: Array, //Captura el array a controlar en el body. Es decir la informacion del CRUD
        idModal: String, //String que determina el nombre del modal
    },
    data(){
        return {
            controlModal: null
        }
    },
    mounted(){
        this.controlModal = document.getElementById(this.idModal)
        //Incializamos el listener
        this.controlModal.addEventListener('shown.bs.modal', this.openModal)
    },
    beforeUnmount(){
        //Removemos el listener
        window.removeEventListener('show.bs.modal',this.openModal)
    },
    methods:{
        /**
         * Metodo que se encarga de controlar el modal.
         * No recibe parametros
         */
        openModal(){ this.controlModal.focus() }
    },
    components: { FontAwesome }
}
</script>
