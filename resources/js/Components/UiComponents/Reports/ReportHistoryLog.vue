<template>
    <div>
        <Loading :active="!scope.isMounted"></Loading>
        <div class="reports-container" v-if="scope.isMounted">
            <span class="reports-container-title">Ingrese información del usuario</span>
            <div class="reports-container-search">
                <div class="input-group mb-3">
                    <span class="input-group-text">Nombre</span>
                    <Multiselect v-model="inputCodeUser" :options="multiSelectUser" placeholder="Seleccione un usuario"
                        mode="single" class="form-control" :searchable="true"></Multiselect>
                </div>
                <div class="input-group mb-3" v-if="inputCodeUser !== null">
                    <span class="input-group-text">Hora</span>
                    <select class="form-select form-control" v-model="inputTypeSelect" title="ConceptSelect"
                        autocomplete="nope">
                        <option v-for="(option, type) in typeOptions" :key="type" :value="type" :selected="type == 0"
                            :disabled="type == 0">{{
                                option }}</option>
                    </select>
                </div>
            </div>
        </div>
        <ListingCrud style="width: 90%;" v-if="scope.isMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte historico de usuario" not-found-message="Rellene la informacion del usuario"
            :select-search="selectSearch" view-search view-excel :title-excel="titleReportExcel" white-space
            status-table="usuarios">
        </ListingCrud>
    </div>
</template>
<script>
import ListingCrud from '@/Components/ListingCrud.vue';
import Loading from '@/Components/Loading.vue';
import Multiselect from '@vueform/multiselect';
import { globalMethodsReport } from './GlobalReportMethods';
import axios from 'axios';
export default {
    props: {
        scope: Object //Importa la data del padre
    },
    data() {
        return {
            reportColumns: {
                column1: 'Código',
                column2: "Nombre",
                column3: 'Fecha',
                column4: 'Concepto',
                column5: 'Proyecto',
                column6: 'Estatus',
            },
            selectSearch: {
                select1: "Fecha desde",
                select2: "Fecha hasta",
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
            inputCodeUser: null, //Codigo del usuario a seleccionar
            inputTypeSelect: 0, //Almacena el tipo de horas
            multiSelectUser: [], // Array de objetos del multiselect de usuarios
            typeOptions: ["Selecione un tipo de hora", "Proyectos", "Administrativo", "Ambos"],
            titleReportExcel: ""
        }
    },
    created() {
        this.isListMounted = false;
        this.$emit('update-mounted', this.isListMounted)
        axios.post("/reports/list-users")
            .then(request => {
                this.isListMounted = true;
                this.$emit('update-mounted', this.isListMounted)
                request.data.message.forEach(users => {
                    this.multiSelectUser.push({
                        value: users.user_code,
                        label: `${users.user_code} — ${users.user_name}`,
                        disabled: false
                    })
                })
            })
    },
    watch: {
        inputCodeUser(codeUser) {
            this.inputTypeSelect = 0
            if (codeUser !== null) {
                const indexUser = this.multiSelectUser.map(user => user.value).indexOf(codeUser)
                //Capturamos el nombre
                const userName = this.multiSelectUser[indexUser].label.split(' — ')[1];
                //Colocamos el nombre del archivo
                this.titleReportExcel = `Reporte historico de horas de ${userName.toLowerCase()}.xls`
            }
        },
        inputTypeSelect(typeHour) {
            this.directiveList = []
            try {
                //Si el selector es 0 no mostramos nada
                if (typeHour == 0) throw "NoList";
                //Caso contrario
                this.isListMounted = false;
                this.$emit('update-mounted', this.isListMounted)
                axios.post("/reports/list-no-history-hour", { userCode: this.inputCodeUser })
                    .then(request => {
                        this.isListMounted = true;
                        this.$emit('update-mounted', this.isListMounted)
                        if (typeHour != 3) {
                            if (typeHour == 2) this.selectSearch["select3"] = "Concepto";
                            if (typeHour == 1) this.selectSearch["select3"] = "Proyecto";

                            request.data.message.forEach((historyData) => {
                                if (historyData.type == typeHour) {
                                    this.directiveList.push({
                                        "código": historyData.user_code,
                                        nombre: historyData.user_name,
                                        fecha: historyData.register_date,
                                        concepto: historyData.concept_description,
                                        proyecto: historyData.proy_description,
                                        horas_registradas: this.formatReportNumber(historyData.register_hour)
                                    })
                                }
                            });
                        } else {
                            this.selectSearch = {
                                ...this.selectSearch,
                                select3: "Concepto",
                                select4: "Proyecto"
                            }
                            request.data.message.forEach(historyData => {
                                this.directiveList.push({
                                    "código": historyData.user_code,
                                    nombre: historyData.user_name,
                                    fecha: historyData.register_date,
                                    concepto: historyData.concept_description,
                                    proyecto: historyData.proy_description,
                                    horas_registradas: this.formatReportNumber(historyData.register_hour)
                                })
                            })
                        }
                        //Acomodamos la longitud minima y su paginacion
                        if (this.directiveList.length > 0 && this.directiveList.length < 50) this.directiveLength = this.directiveList.length;
                        this.directivePaginatio = Math.ceil(this.directiveList.length / this.directiveLength);
                    })
                    .catch(error => {
                        console.error(error)
                    })
            } catch (error) {
                this.directivePaginatio == 0
            }
        }
    },
    mixins: [globalMethodsReport],
    components: { ListingCrud, Loading, Multiselect }
}
</script>
