<template>
    <div class="table-responsive" v-if="active">
        <table class="table table-hover">
            <thead>
            <tr>
                <!-- Table Object -->
            </tr>
            </thead>
            <tbody>
            <tr>
                <!-- Consult Resolve -->
            </tr>
            <tr>
                <th scope="row">2</th>
                <td>Jacob</td>
                <td>Thornton</td>
                <td>@fat</td>
            </tr>
            <tr>
                <th scope="row">3</th>
                <td colspan="2">Larry the Bird</td>
                <td>@twitter</td>
            </tr>
            </tbody>
        </table>
    </div>
    <!-- TODO: Pagination Reservation -->
</template>

<script>
// Import all of Bootstrap's JS
import '../bootstrap'

export default {
    props: {
        titleObject: Object,
        /*Objeto que indica el titulo de cada Columna.
        Debe existir una propiedad llamada settings y si tiene más de 1 opción*/
        paginationLenght: Number, //La cantidad de páginas
        paginationLimit: Number, // El Tamaño máximo de cada página
        tableTarget: String, // La tabla que se mostrará
        active: Boolean //Controla el estado de la tabla
    },
    data()
    {
        return {
            activeClass: { target: false, class: 'table-active'} //Selecciona alguna tabla
        }
    },

}
</script>
