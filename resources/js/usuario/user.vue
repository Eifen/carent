<template>
    <tr>
        <td>{{ user.codigo }}</td>
        <td>{{ user.cedula }}</td>
        <td>{{ user.nombre }}</td>
        <td>{{ user.correo_principal }}</td>
        <td>
            <span :class="badgeClass(user.estatus)">{{ user.estatus }}</span>
        </td>
        <td>
            <div class="dropdown">
                <i aria-expanded="false" class="bi bi-gear" data-bs-toggle="dropdown"></i>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" data-bs-target="#editGameModal" data-bs-toggle="modal">Ver detalles</a>
                    </li>
                    <li>
                        <a class="dropdown-item">Permisos del sistema</a>
                    </li>
                </ul>
            </div>
        </td>
    </tr>
</template>


<script>

var self

export default {
    props: ['user'],
    data() {
        return {
            canUpdate: false
        }
    },
    beforeCreate: function() {
        self = this
    },
    mounted: function () {},
    methods: {

        badgeClass: (status) => {

            let classes = {
                activo: "badge text-bg-success",
                inactivo: "badge text-bg-danger"
            }

            return (classes[status]) ? classes[status] : "badge text-bg-primary"

        },
        mostrarDetalleUsuario: () => {},

    }
}

</script>

<style lang="less" scoped src="../../less/usuario/user.less"></style>
