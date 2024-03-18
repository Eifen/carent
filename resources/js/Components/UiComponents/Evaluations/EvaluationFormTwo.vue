<template>
    <div>
        <h2 class="evaluation-title"> Evaluación de Desempeño </h2>
        <h3 class="evaluation-subtitle"> Periodo a Evaluar: 2023/04/01 al 2024/10/01 </h3>
        <div class="evaluation-form-two">
            <!-- <div class="buttonCRUD" id="button-back" @click="$emit('return')">Regresar</div> -->
            <div class="evaluation-form-two-label" id="data-activity">Datos de Actividades</div>
            <div class="evaluation-form-two-label" id="general-features">Aspectos Generales</div>
            <div class="evaluation-form-two-label" id="comments-evaluator">Comentarios del Evaluador</div>
            <div class="evaluation-form-two-label" id="promotions">Ascensos</div>
            <div class="evaluation-form-two-label" id="approvals">Aprobaciones</div>
            <!-- Datos de tabla del medio Left -->
            <div class="evaluation-form-two-mid-left">
                <!-- Label  -->
                <div for="mid-client-code">Código</div>
                <div for="mid-order-name">Nombre del Encargo</div>
                <div for="mid-director">Director/Gerente/Ecargado</div>
                <div for="mid-hours-loaded">Horas Cargadas</div>
                <!-- Contenido del label -->
                <div for="mid-client-code1">2230</div>
                <div for="mid-client-code2">1359</div>
                <div for="mid-client-code3">2272</div>
                <div for="mid-client-code4">1738</div>
                <div for="mid-client-code5">2231</div>
                <!-- <div for="mid-client-code6"></div>
                <div for="mid-client-code7"></div>
                <div for="mid-client-code8"></div>
                <div for="mid-client-code9"></div>
                <div for="mid-client-code10"></div>
                <div for="mid-client-code11"></div>
                <div for="mid-client-code12"></div> -->
                <!-- Contenido del label -->
                <div for="mid-order-name1">ISQM1</div>
                <div for="mid-order-name2">INFORME DE RIESGO</div>
                <div for="mid-order-name3">INFORME DE RIESGO, PREVENCIÓN</div>
                <div for="mid-order-name4">INFORME DE PREVENCIÓN</div>
                <div for="mid-order-name5">INFORME DE RIESGO Y PREVENCIÓN</div>
                <!-- <div for="mid-order-name6"></div>
                <div for="mid-order-name7"></div>
                <div for="mid-order-name8"></div>
                <div for="mid-order-name9"></div>
                <div for="mid-order-name10"></div>
                <div for="mid-order-name11"></div>
                <div for="mid-order-name12"></div> -->
                <!-- Contenido del label -->
                <div for="mid-director1">Angela Aranea</div>
                <div for="mid-director2">Angela Aranea</div>
                <div for="mid-director3">Angela Aranea</div>
                <div for="mid-director4">Angela Aranea</div>
                <div for="mid-director5">Angela Aranea</div>
                <!-- <div for="mid-director6"></div>
                <div for="mid-director7"></div>
                <div for="mid-director8"></div>
                <div for="mid-director9"></div>
                <div for="mid-director10"></div>
                <div for="mid-director11"></div>
                <div for="mid-director12"></div> -->
                <!-- Contenido del label -->
                <div for="mid-hours-loaded1">452</div>
                <div for="mid-hours-loaded2">24</div>
                <div for="mid-hours-loaded3">24</div>
                <div for="mid-hours-loaded4">30</div>
                <div for="mid-hours-loaded5">10</div>
                <!-- <div for="mid-hours-loaded6"></div>
                <div for="mid-hours-loaded7"></div>
                <div for="mid-hours-loaded8"></div>
                <div for="mid-hours-loaded9"></div>
                <div for="mid-hours-loaded10"></div>
                <div for="mid-hours-loaded11"></div>
                <div for="mid-hours-loaded12"></div> -->
                <!-- Contenido del label -->
                <div for="total-hours-loaded"> TOTAL HORAS CARGADAS EN CLIENTES </div>
                <div for="result-hours-loaded"> 540 </div>
                <div for="loadbility-average"> PORCENTAJE DE CARGABILIDAD DEL CLIENTE </div>
                <div for="result-loadbility-average"> 0.00% </div>
                <div for="participation-average"> PORCENTAJE DE PARTICIPACIÓN DE CAPACITACIÓN </div>
                <div for="result-participation-average"> 50.00% </div>
                <!-- Actividades realizadas Right  -->
                <div for="activities-done-top">Actividades Realizadas</div>
                <textarea for="activities-done1"></textarea>
                <textarea for="activities-done2"></textarea>
                <textarea for="activities-done3"></textarea>
                <textarea for="activities-done4"></textarea>
                <textarea for="activities-done5"></textarea>
                <!-- <textarea for="activities-done6"></textarea>
                <textarea for="activities-done7"></textarea>
                <textarea for="activities-done8"></textarea>
                <textarea for="activities-done9"></textarea>
                <textarea for="activities-done10"></textarea>
                <textarea for="activities-done11"></textarea>
                <textarea for="activities-done12"></textarea> -->
                <div for="evaluated-capacitation"> CAPACITACIÓN EN LA QUE PARTICIPÓ EL EVALUADO </div>
                <textarea rows="1" for="evaluated-capacitation1"></textarea>
                <textarea rows="1" for="evaluated-capacitation2"></textarea>
                <textarea rows="1" for="evaluated-capacitation3"></textarea>
                <textarea rows="1" for="evaluated-capacitation4"></textarea>
                <textarea rows="1" for="evaluated-capacitation5"></textarea>
            </div>
            <!-- Datos tabla aspectos generales -->
            <div class="evaluation-form-two-general">
                <!-- Label  -->
                <div for="general-features-top1">1.- ¿Ha realizado cursos de espacialización(estudios de post grado,
                    diplomados magister, entre otros)?</div>
                <textarea rows="2" cols="1/9"></textarea>
                <div for="general-features-top2">2.- ¿Posee dominio de otro idioma distinto al nativo? Indique cuales y el
                    nivel de conocimiento que posee de los mismos</div>
                <textarea rows="2" cols="1/9"></textarea>
                <div for="general-features-top3">3.- ¿Colaboró usted en la formación de equipos productivos e integrados a
                    los objetivos de la Firma?</div>
                <textarea rows="2" cols="1/9"></textarea>
                <div for="general-features-top4">4.- ¿Cumplió usted con la política establecida en la Firma de reportarse a
                    diario antes de las 9:30 a.m.?</div>
                <textarea rows="2" cols="1/9"></textarea>
                <div for="general-features-top5">5.- Enuncie las principales fortalezas o aspectos positivos que demuestra
                    el trabajador en el desempeño de sus funciones y en la ejecución de su trabajo en general, relaciados
                    con sus comportamientos, competencias técnicas o conocimientos:</div>
                <textarea rows="2" cols="1/9"></textarea>
                <div for="general-features-top6">6.- ¿Describa cuáles considera que son las áreas de atención o de mejora
                    que presenta el trabajador con respecto a comportamientos, competencias, técnicas o conocimientos</div>
                <textarea rows="2" cols="1/9"></textarea>
            </div>
            <!-- Datos tabla comentarios -->
            <div class="evaluation-form-two-comments">
                <textarea rows="2" cols="1/9"></textarea>
            </div>
            <!-- Datos de tabla del Ascensos Left -->
            <div class="evaluation-form-two-promotions">
                <!-- Label  -->
                <div for="last-promotion">Último Ascenso</div>
                <div for="date-promotion">Fecha</div>
                <div for="position">Cargo</div>
                <div for="proposed-promotion">Ascenso Propuesto</div>
                <!-- Contenido del label -->
                <div for="date-promotion1">02/10/2022</div>
                <!-- Contenido del label -->
                <div for="position1">Asistente I</div>
                <!-- Contenido del label -->
                <div for="proposed-promotion1">Asistente II</div>
            </div>
            <!-- Datos de tabla del Aprobaciones Right -->
            <div class="evaluation-form-two-approvals">
                <!-- Label  -->
                <div for="approvals-evaluator">Evaluador</div>
                <div for="approvals-evaluated">Evaluado</div>
                <!-- Contenido del label -->
                <div for="approvals-evaluator1">Enrique Rodriguez</div>
                <div for="approvals-evaluated1">César Garcia</div>


            </div>


            <div style="display: grid;grid-area: 49 / 1 / 50 / 9;margin-top: 16px;">
                <div class="modal-footer" for="dashboard">
                    <div class="dashboard-form-container-form-button" v-if="!isClick" @click="NextPage()">
                        <span v-if="!isClick">Guardar</span>
                    </div>
                    <span v-else-if="isClick">
                        <font-awesome string-icon="fa-solid fa-spinner" is-spin></font-awesome>
                    </span>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
</script>
