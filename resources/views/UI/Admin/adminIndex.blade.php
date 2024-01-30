<div id="admin-index">
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
</div>
