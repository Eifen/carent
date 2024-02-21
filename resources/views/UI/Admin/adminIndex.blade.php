<div id="admin-index" class="reports-container">
    <!-- Fechas  -->
    <span class="reports-container-title">Ingrese el intervalo de fechas</span>
    <div class="reports-container-search">
        <div class="input-group mb-3">
            <span class="input-group-text">Fecha desde</span>
            <input type="text" class="form-control" placeholder="Ejemplo: 1990-02-18" id="start_date" v-model="dateStart"
                disabled />
            <span class="input-group-text" for="calendar">
                <calendar @to-input="dateSearch($event, 'start')" :key="dateStart"></calendar>
            </span>
        </div>
        <div class="input-group mb-3">
            <span v-if="dateStart.length != 0" class="input-group-text">Fecha Hasta</span>
            <input v-if="dateStart.length != 0" type="text" class="form-control" placeholder="Ejemplo: 1990-02-18"
                id="end_date" v-model="dateEnd" disabled />
            <span v-if="dateStart.length != 0" class="input-group-text" for="calendar">
                <calendar @to-input="dateSearch($event, 'end')"></calendar>
            </span>
        </div>
    </div>
    {{-- Reporte menores al 99% --}}
    <div v-if="dateEnd.length != 0" class="reports-container-list">
        <div class="buttonCRUD" @click="refixAll" v-if="isMounted">Acomodar a todos</div>
        <loading :active="!isMounted"></loading>
        <listing-crud v-if="isMounted && directivePaginatio != 0 && refTotal != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Personas entre 90 y 99.9%" not-found-message="No hay horas cargadas"
            :select-search="selectSearch" view-search status-table="usuarios" view-hours :hours-ref="refTotal"
            :is-admin="1" :key="directiveList" @columnS1Target="refixUser">
        </listing-crud>
    </div>
</div>
