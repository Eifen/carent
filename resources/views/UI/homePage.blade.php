<div id="home-page" v-cloak>
    <loading :active="!isMounted"></loading>
    <div class="home-container" v-if="isMounted">
        <div class="home-container-graph" id="graph">
            <Bar id="graph-month" :options="updateOptions" :data="updateChart"></Bar>
        </div>
        <div class="home-container-info">
            <div class="home-container-info-title">Este es el avance de tu cargabilidad para @{{ chartData.datasets[0].label }}</div>
            <div class="home-container-info-card">
                <div>Horas estimadas</div>
                <span>@{{ listData.estimated_hour }}</span>
            </div>
            <div class="home-container-info-card" :class="percenStyle(percenTotal)">
                <div>Horas reales cargadas al carent</div>
                <span>@{{ totalHours }}</span>
            </div>
            <div class="home-container-info-card">
                <div>Horas estimadas a proyectos</div>
                <span>@{{ listData.estimated_proy.toFixed(0) }}</span>
            </div>
            <div class="home-container-info-card"
                :class="percenStyle((percenProy == 0 && listData.estimated_proy == 0 ? 100 : percenProy), true)">
                <div>Horas a proyectos cargadas al carent</div>
                <span>@{{ listData.real_proy }}</span>
            </div>
            <div class="home-container-info-card">
                <div>Horas estimadas a administración</div>
                <span>@{{ listData.estimated_admon.toFixed(0) }}</span>
            </div>
            <div class="home-container-info-card" :class="percenStyle(percenAdmon, true)">
                <div>Horas a administración cargadas al carent</div>
                <span>@{{ listData.real_admon }}</span>
            </div>
            <div class="home-container-info-diff">
                <div class="home-container-info-diff-text">Horas a proyectos por cargar <br> @{{ (listData.estimated_proy - listData.real_proy).toFixed(0) }}
                </div>
                <div class="home-container-info-diff-text">Horas admon aprobadas <br> @{{ listData.real_admon }}</div>
                <div class="home-container-info-diff-text">Horas admon por aprobar <br> @{{ listData.real_admon_no_approved }}</div>
            </div>
            <div class="home-container-info-legend">
                <div class="legend-title">Leyenda</div>
                <span class="square" id="square-danger"></span> porcentajes menores al 50%<br>
                <span class="square" id="square-warning"></span> porcentajes entre el 50 y el 99%<br>
                <span class="square" id="square-success"></span> porcentajes iguales al 100%<br>
            </div>
            <div class="home-container-info-legend">
                <div class="legend-title">Procentajes de carga</div>
                <b>Total:</b> @{{ formatPercen(percenTotal) }}%<br>
                <b>Proy:</b> @{{ percenProy == 0 && listData.estimated_proy == 0 ? 100 : formatPercen(percenProy) }}%<br>
                <b>Admon:</b> @{{ formatPercen(percenAdmon) }}%<br>
            </div>
        </div>
    </div>
</div>
