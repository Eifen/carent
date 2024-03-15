<template>
    <div>
        <div class="container-lg" v-if="alreadyeva === true">
            <div class="alert alert-danger" role="alert">
                Ya existe un registro de esta Autoevaluación!
            </div>
        </div>
        <div class="container-lg" v-if="alreadyeva === false">
            <!-- Content here -->
            <h2 class="text-center"> Evaluación de Desempeño </h2>
            <h3 class="text-center"> Periodo a Evaluar: {{ dte.dateevaperiod.datefrom }} al
                {{ dte.dateevaperiod.dateuntil }} </h3>
            <div class="bg-light">
                <!-- Content here -->
                <div class="row row-cols-2 text-center">
                    <div class="col ">
                        <h5 class="text-center text-light" style="background: #091f40">Datos del Empleado</h5>
                        <div class="row mb-1">
                            <div class="col-4" style="background: #d1d7dd">Código</div>
                            <div class="col">{{ dte.evaluado.user_id }}</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-4" style="background: #d1d7dd"> Fecha de Ingreso</div>
                            <div class="col">{{ dte.evaluado.admission_date }}</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-4" style="background: #d1d7dd">Nombre y Apellido</div>
                            <div class="col">{{ dte.evaluado.first_name }} {{ dte.evaluado.first_surname }}</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-4" style="background: #d1d7dd">Cargo Actual</div>
                            <div class="col">{{ dte.evaluado.position_name }}</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-4" style="background: #d1d7dd">Fecha autoevaluación</div>
                            <div class="col">{{ dte.evaluation_au_date ? dte.evaluation_au_date : '' }}</div>
                        </div>
                    </div>
                    <div class="col ">
                        <h5 class="text-center text-light" style="background: #091f40">Datos del Evaluador</h5>
                        <div class="row mb-1">
                            <div class="col-4" style="background: #d1d7dd">Código</div>
                            <div class="col">{{ dte.evaluador.user_id }}</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-4" style="background: #d1d7dd">Nombre y Apellido</div>
                            <div class="col">{{ dte.evaluador.first_name }} {{ dte.evaluador.first_surname }}</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-4" style="background: #d1d7dd">Cargo Actual</div>
                            <div class="col">{{ dte.evaluador.position_name }}</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-4" style="background: #d1d7dd"> Fecha de Evaluación</div>
                            <div class="col">{{ dte.evaluation_date ? dte.evaluation_date : '' }}</div>
                        </div>
                    </div>
                </div>
                <!-- Content here -->
                <div class="row row-cols-1 text-center">
                    <div class="col my-2" style="background: #FFFFFF">
                        <strong><span class="text-black">ESCRIBA:</span></strong>
                        <strong><span class="text-danger">SI ES 0 "NO APLICA".</span></strong>
                        <strong><span class="text-info">DEL 1 AL 2 POR DEBAJO DE LO ESPERADO.</span></strong>
                        <strong><span class="text-primary">DEL 3 AL 4 ESPERADO.</span></strong>
                        <strong><span class="text-success">DEL 5 AL 6 POR ENCIMA DE LO ESPERADO.</span></strong>
                    </div>

                </div>
                <!-- Content here seccion1 -->
                <div class="row">
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-center text-light" style="background: #091f40">Habilidad Analítica</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Autoevaluación</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Evaluación</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Observaciones</h5>
                        </div>
                    </div>
                    <div class="row" v-for="(item, index) in questions.seccion1">
                        <div class="col-6">
                            <div class="row align-items-center">
                                <div class="col-1 text-center py-2" style="background: #d1d7dd">{{ index }}</div>
                                <div class="col " style="font-size: 10pt">
                                    {{ item }}
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" :min="0" :max="6" :maxlength="1"
                                        v-model="form[0][index].auto" :disabled="isInfo" @change="averagetotal(0, 0)"
                                        :readonly="dte.evatype === 1">
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" :min="0" :max="6" :maxlength="1"
                                        v-model="form[0][index].eva" @keyup="onKeyup" @keydown.prevent
                                        @keydown.delete.prevent :disabled="isInfo" @change="averagetotal(0, 1)"
                                        :readonly="dte.evatype === 0">
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <textarea class="form-control" id="floatingTextarea" :disabled="isInfo"
                                        v-model="form[0][index].come" style="height: 50px"
                                        :readonly="dte.evatype === 0"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="background: #d1d7dd">
                        <div class="col-6" style="background: #d1d7dd">
                            <div class="row align-items-center">
                                <div class="col text-center" style="font-size: 12pt">
                                    TOTAL (HABILIDAD ANALITICA)
                                </div>
                            </div>
                        </div>
                        <div class="col-2" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" v-model="formtotals[0].auto" step="0.10"
                                        readonly :disabled="isInfo">
                                </div>
                            </div>
                        </div>
                        <div class="col-2" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" v-model="formtotals[0].eva" step="0.10"
                                        readonly :disabled="isInfo">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Content here seccion2 -->
                <div class="row">
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-center text-light" style="background: #091f40">Conocimientos técnicos</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Autoevaluación</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Evaluación</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Observaciones</h5>
                        </div>
                    </div>
                    <div class="row" v-for="(item, index) in questions.seccion2">
                        <div class="col-6">
                            <div class="row align-items-center">
                                <div class="col-1 text-center py-2" style="background: #d1d7dd">{{ index }}</div>
                                <div class="col " style="font-size: 10pt">
                                    {{ item }}
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" :min="0" :max="6" :maxlength="1"
                                        v-model="form[1][index].auto" @keyup="onKeyup" @keydown.prevent
                                        @keydown.delete.prevent :disabled="isInfo" @change="averagetotal(1, 0)"
                                        :readonly="dte.evatype === 1">
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" :min="0" :max="6" :maxlength="1"
                                        v-model="form[1][index].eva" @keyup="onKeyup" @keydown.prevent
                                        @keydown.delete.prevent :disabled="isInfo" @change="averagetotal(1, 1)"
                                        :readonly="dte.evatype === 0">
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <textarea class="form-control" id="floatingTextarea" :disabled="isInfo"
                                        v-model="form[1][index].come" style="height: 50px"
                                        :readonly="dte.evatype === 0"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="background: #d1d7dd">
                        <div class="col-6" style="background: #d1d7dd">
                            <div class="row align-items-center">
                                <div class="col text-center" style="font-size: 12pt">
                                    TOTAL
                                </div>
                            </div>
                        </div>
                        <div class="col-2" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" v-model="formtotals[1].auto" step="0.10"
                                        readonly @keydown.prevent @keydown.delete.prevent :disabled="isInfo">
                                </div>
                            </div>
                        </div>
                        <div class="col-2" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" v-model="formtotals[1].eva" step="0.10"
                                        readonly @keydown.prevent @keydown.delete.prevent :disabled="isInfo">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Content here seccion3 -->
                <div class="row">
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-center text-light" style="background: #091f40">Papeles de trabajo</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Autoevaluación</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Evaluación</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Observaciones</h5>
                        </div>
                    </div>
                    <div class="row" v-for="(item, index) in questions.seccion3">
                        <div class="col-6">
                            <div class="row align-items-center">
                                <div class="col-1 text-center py-2" style="background: #d1d7dd">{{ index }}</div>
                                <div class="col " style="font-size: 10pt">
                                    {{ item }}
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" :min="0" :max="6" :maxlength="1"
                                        v-model="form[2][index].auto" @keyup="onKeyup" @keydown.prevent
                                        @keydown.delete.prevent :disabled="isInfo" @change="averagetotal(2, 0)"
                                        :readonly="dte.evatype === 1">
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" :min="0" :max="6" :maxlength="1"
                                        v-model="form[2][index].eva" @keyup="onKeyup" @keydown.prevent
                                        @keydown.delete.prevent :disabled="isInfo" @change="averagetotal(2, 1)"
                                        :readonly="dte.evatype === 0">
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <textarea class="form-control" id="floatingTextarea" :readonly="dte.evatype === 0"
                                        v-model="form[2][index].come" style="height: 50px"
                                        :disabled="isInfo"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="background: #d1d7dd">
                        <div class="col-6" style="background: #d1d7dd">
                            <div class="row align-items-center">
                                <div class="col text-center" style="font-size: 12pt">
                                    TOTAL
                                </div>
                            </div>
                        </div>
                        <div class="col-2" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" v-model="formtotals[2].auto" step="0.10"
                                        readonly @keydown.prevent @keydown.delete.prevent :disabled="isInfo">
                                </div>
                            </div>
                        </div>
                        <div class="col-2" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" v-model="formtotals[2].eva" step="0.10"
                                        readonly @keydown.prevent @keydown.delete.prevent :disabled="isInfo">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Content here seccion4 -->
                <div class="row">
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-center text-light" style="background: #091f40">Responsabilidad y
                                productividad</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Autoevaluación</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Evaluación</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Observaciones</h5>
                        </div>
                    </div>
                    <div class="row" v-for="(item, index) in questions.seccion4">
                        <div class="col-6">
                            <div class="row align-items-center">
                                <div class="col-1 text-center py-2" style="background: #d1d7dd">{{ index }}</div>
                                <div class="col " style="font-size: 10pt">
                                    {{ item }}
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" :min="0" :max="6" :maxlength="1"
                                        v-model="form[3][index].auto" @keyup="onKeyup" @keydown.prevent
                                        @keydown.delete.prevent :disabled="isInfo" @change="averagetotal(3, 0)"
                                        :readonly="dte.evatype === 1">
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" :min="0" :max="6" :maxlength="1"
                                        v-model="form[3][index].eva" @keyup="onKeyup" @keydown.prevent
                                        @keydown.delete.prevent :disabled="isInfo" @change="averagetotal(3, 1)"
                                        :readonly="dte.evatype === 0">
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <textarea class="form-control" id="floatingTextarea" :readonly="dte.evatype === 0"
                                        v-model="form[3][index].come" style="height: 50px"
                                        :disabled="isInfo"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="background: #d1d7dd">
                        <div class="col-6" style="background: #d1d7dd">
                            <div class="row align-items-center">
                                <div class="col text-center" style="font-size: 12pt">
                                    TOTAL
                                </div>
                            </div>
                        </div>
                        <div class="col-2" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" v-model="formtotals[3].auto" step="0.10"
                                        readonly @keydown.prevent @keydown.delete.prevent :disabled="isInfo">
                                </div>
                            </div>
                        </div>
                        <div class="col-2" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" v-model="formtotals[3].eva" step="0.10"
                                        readonly @keydown.prevent @keydown.delete.prevent :disabled="isInfo">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Content here seccion5 -->
                <div class="row">
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-center text-light" style="background: #091f40">Atención al cliente</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Autoevaluación</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Evaluación</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Observaciones</h5>
                        </div>
                    </div>
                    <div class="row" v-for="(item, index) in questions.seccion5">
                        <div class="col-6">
                            <div class="row align-items-center">
                                <div class="col-1 text-center py-2" style="background: #d1d7dd">{{ index }}</div>
                                <div class="col " style="font-size: 10pt">
                                    {{ item }}
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" :min="0" :max="6" :maxlength="1"
                                        v-model="form[4][index].auto" @keyup="onKeyup" @keydown.prevent
                                        @keydown.delete.prevent :disabled="isInfo" @change="averagetotal(4, 0)"
                                        :readonly="dte.evatype === 1">
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" :min="0" :max="6" :maxlength="1"
                                        v-model="form[4][index].eva" @keyup="onKeyup" @keydown.prevent
                                        @keydown.delete.prevent :disabled="isInfo" @change="averagetotal(4, 1)"
                                        :readonly="dte.evatype === 0">
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <textarea class="form-control" id="floatingTextarea" :readonly="dte.evatype === 0"
                                        v-model="form[4][index].come" style="height: 50px"
                                        :disabled="isInfo"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="background: #d1d7dd">
                        <div class="col-6" style="background: #d1d7dd">
                            <div class="row align-items-center">
                                <div class="col text-center" style="font-size: 12pt">
                                    TOTAL
                                </div>
                            </div>
                        </div>
                        <div class="col-2" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" v-model="formtotals[4].auto" step="0.10"
                                        readonly @keydown.prevent @keydown.delete.prevent :disabled="isInfo">
                                </div>
                            </div>
                        </div>
                        <div class="col-2" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" v-model="formtotals[4].eva" step="0.10"
                                        readonly @keydown.prevent @keydown.delete.prevent :disabled="isInfo">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Content here seccion6 -->
                <div class="row">
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-center text-light" style="background: #091f40">Personalidad</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Autoevaluación</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Evaluación</h5>
                        </div>
                        <div class="col-2">
                            <h5 class="text-center text-light" style="background: #091f40">Observaciones</h5>
                        </div>
                    </div>
                    <div class="row" v-for="(item, index) in questions.seccion6">
                        <div class="col-6">
                            <div class="row align-items-center">
                                <div class="col-1 text-center py-2" style="background: #d1d7dd">{{ index }}</div>
                                <div class="col " style="font-size: 10pt">
                                    {{ item }}
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" :min="0" :max="6" :maxlength="1"
                                        v-model="form[5][index].auto" @keyup="onKeyup" @keydown.prevent
                                        @keydown.delete.prevent :disabled="isInfo" @change="averagetotal(5, 0)"
                                        :readonly="dte.evatype === 1">
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" :min="0" :max="6" :maxlength="1"
                                        v-model="form[5][index].eva" @keyup="onKeyup" @keydown.prevent
                                        @keydown.delete.prevent :disabled="isInfo" @change="averagetotal(5, 1)"
                                        :readonly="dte.evatype === 0">
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="row">
                                <div class="col">
                                    <textarea class="form-control" id="floatingTextarea" :readonly="dte.evatype === 0"
                                        v-model="form[5][index].come" style="height: 50px"
                                        :disabled="isInfo"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="background: #d1d7dd">
                        <div class="col-6" style="background: #d1d7dd">
                            <div class="row align-items-center">
                                <div class="col text-center" style="font-size: 12pt">
                                    TOTAL
                                </div>
                            </div>
                        </div>
                        <div class="col-2" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" v-model="formtotals[5].auto" step="0.10"
                                        readonly @keydown.prevent @keydown.delete.prevent :disabled="isInfo">
                                </div>
                            </div>
                        </div>
                        <div class="col-2" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" type="number" v-model="formtotals[5].eva" step="0.10"
                                        readonly @keydown.prevent @keydown.delete.prevent :disabled="isInfo">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Content here second page  -->
                <div class="row">
                    <div class="row text-center">
                        <div class="col">
                            <h5 class="text-center text-light" style="background: #091f40">Datos de Actividades</h5>
                        </div>
                    </div>
                    <div class="row text-center" style="background: #d1d7dd">
                        <div class="col-1">
                            <h5 class="text-center" style="font-size: 10pt">Código</h5>
                        </div>
                        <div class="col-4">
                            <h5 class="text-center" style="font-size: 10pt">Nombre del Encargo</h5>
                        </div>
                        <div class="col-3">
                            <h5 class="text-center" style="font-size: 10pt">Director/Gerente/Ecargado</h5>
                        </div>
                        <div class="col-1">
                            <h5 class="text-center" style="font-size: 10pt">Horas</h5>
                        </div>
                        <div class="col-3">
                            <h5 class="text-center" style="font-size: 10pt">Actividades Realizadas</h5>
                        </div>
                    </div>
                    <div class="row text-center" v-for="(item, index) in formact">
                        <div class="col-1" style="font-size: 10pt">
                            {{ item.project_hour_id }}
                        </div>
                        <div class="col-4" style="font-size: 10pt">
                            {{ item.project_load_observation }}
                        </div>
                        <div class="col-3" style="font-size: 10pt">
                            {{ dte.evaluador.first_name }} {{ dte.evaluador.first_surname }}
                        </div>
                        <div class="col-1" style="font-size: 10pt">
                            {{ item.register_hour }}
                        </div>
                        <div class="col-3">
                            <textarea class="form-control" id="floatingTextarea" v-model="item.act" style="height: 50px"
                                :disabled="isInfo"></textarea>
                        </div>
                    </div>
                    <div class="row" style="background: #d1d7dd">
                        <div class="col-6" style="background: #d1d7dd">
                            <div class="row align-items-center">
                                <div class="col-10 text-center mt-4" style="font-size: 12pt">
                                    TOTAL HORAS CARGADAS EN CLIENTES
                                </div>
                                <div class="col-2 text-center bg-light mt-4" style="font-size: 12pt">
                                    {{ formacttotal.totalh }}
                                </div>
                                <!--                                <div class="col-10 text-center mt-3" style="font-size: 12pt">-->
                                <!--                                    PORCENTAJE DE CARGABILIDAD DEL CLIENTE-->
                                <!--                                </div>-->
                                <!--                                <div class="col-2 text-center bg-light mt-3" style="font-size: 12pt">-->
                                <!--                                    {{  formacttotal.averagecc }}%-->
                                <!--                                </div>-->
                                <!--                                <div class="col-10 text-center mt-3" style="font-size: 12pt">-->
                                <!--                                    PORCENTAJE DE PARTICIPACIÓN DE CAPACITACIÓN-->
                                <!--                                </div>-->
                                <!--                                <div class="col-2 text-center bg-light mt-3" style="font-size: 12pt">-->
                                <!--                                    {{  formacttotal.averagepc }}%-->
                                <!--                                </div>-->
                            </div>
                        </div>
                        <div class="col-6" style="background: #d1d7dd">
                            <div class="row">
                                <div class="col-12 text-center" style="font-size: 12pt">
                                    Actividades Realizadas
                                </div>
                                <div class="col-12" v-for="(item, index) in formactr">
                                    <div class="col-12 text-center bg-light mt-1" style="font-size: 12pt">
                                        {{ item.admin_load_observation }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Content here third page  -->
                <div class="row">
                    <div class="row text-center">
                        <div class="col">
                            <h5 class="text-center text-light" style="background: #091f40">Aspectos Generales</h5>
                        </div>
                    </div>
                    <div class="row mt-1 pb-1" style="background: #d1d7dd">
                        <div class="col-12">
                            <h5 class="" style="font-size: 10pt">
                                1.- ¿Ha realizado cursos de espacialización(estudios de post grado, diplomados magister,
                                entre otros)?
                            </h5>
                        </div>
                        <div class="col-12">
                            <textarea class="form-control" id="floatingTextarea" v-model="formag[0]"
                                style="height: 50px" :readonly="dte.evatype === 1" :disabled="isInfo"></textarea>
                        </div>
                    </div>
                    <div class="row mt-1 pb-1" style="background: #d1d7dd">
                        <div class="col-12">
                            <h5 class="" style="font-size: 10pt">
                                2.- ¿Posee dominio de otro idioma distinto al nativo? Indique cuales y el nivel de
                                conocimiento que posee de los mismos
                            </h5>
                        </div>
                        <div class="col-12">
                            <textarea class="form-control" id="floatingTextarea" v-model="formag[1]"
                                style="height: 50px" :readonly="dte.evatype === 1" :disabled="isInfo"></textarea>
                        </div>
                    </div>
                    <div class="row mt-1 pb-1" style="background: #d1d7dd">
                        <div class="col-12">
                            <h5 class="" style="font-size: 10pt">
                                3.- ¿Colaboró usted en la formación de equipos productivos e integrados a los objetivos
                                de la Firma?
                            </h5>
                        </div>
                        <div class="col-12">
                            <textarea class="form-control" id="floatingTextarea" v-model="formag[2]"
                                style="height: 50px" :readonly="dte.evatype === 1" :disabled="isInfo"></textarea>
                        </div>
                    </div>
                    <div class="row mt-1 pb-1" style="background: #d1d7dd">
                        <div class="col-12">
                            <h5 class="" style="font-size: 10pt">
                                4.- ¿Cumplió usted con la política establecida en la Firma de reportarse a diario antes
                                de las 9:30 a.m.?
                            </h5>
                        </div>
                        <div class="col-12">
                            <textarea class="form-control" id="floatingTextarea" v-model="formag[3]"
                                style="height: 50px" :readonly="dte.evatype === 1" :disabled="isInfo"></textarea>
                        </div>
                    </div>
                    <div class="row mt-1 pb-1" style="background: #d1d7dd">
                        <div class="col-12">
                            <h5 class="" style="font-size: 10pt">
                                5.- Enuncie las principales fortalezas o aspectos positivos que demuestra el trabajador
                                en el desempeño
                                de sus funciones y en la ejecución de su trabajo en general, relaciados con sus
                                comportamientos,
                                competencias técnicas o conocimientos:
                            </h5>
                        </div>
                        <div class="col-12">
                            <textarea class="form-control" id="floatingTextarea" v-model="formag[4]"
                                style="height: 50px" :readonly="dte.evatype === 0" :disabled="isInfo"></textarea>
                        </div>
                    </div>
                    <div class="row mt-1 pb-1" style="background: #d1d7dd">
                        <div class="col-12">
                            <h5 class="" style="font-size: 10pt">
                                6.- ¿Describa cuáles considera que son las áreas de atención o de mejora que presenta el
                                trabajador con respecto a comportamientos, competencias, técnicas o conocimientos
                            </h5>
                        </div>
                        <div class="col-12">
                            <textarea class="form-control" id="floatingTextarea" v-model="formag[5]"
                                style="height: 50px" :readonly="dte.evatype === 0" :disabled="isInfo"></textarea>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col">
                            <h5 class="text-center text-light" style="background: #091f40">Comentarios del Evaluador
                            </h5>
                        </div>
                    </div>
                    <div class="row mt-1 pb-1" style="background: #d1d7dd">
                        <div class="col-12">
                            <textarea class="form-control" id="floatingTextarea" v-model="evaluator_comment"
                                style="height: 50px" :readonly="dte.evatype === 0" :disabled="isInfo"></textarea>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-8">
                            <h5 class="text-center text-light" style="background: #091f40">Ascensos</h5>
                            <div class="row mt-1 pb-1 align-items-center" style="background: #d1d7dd">
                                <div class="col-6">
                                    Último Ascenso
                                    <div class="row mt-1 pb-1" style="background: #d1d7dd">
                                        <div class="col-6">
                                            Fecha
                                        </div>
                                        <div class="col-6">
                                            Cargo
                                        </div>
                                    </div>
                                    <div class="row mt-1 pb-1" style="background: #d1d7dd">
                                        <div class="col-6 bg-light">
                                            {{ dte.potitionp.date_promotion ? dte.potitionp.date_promotion : '' }}
                                        </div>
                                        <div class="col-6 bg-light">
                                            {{ dte.potitionp.position_name ? dte.potitionp.position_name : '' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    Ascenso Propuesto
                                    <div class="row mt-1 pb-1 " style="background: #d1d7dd">
                                        <div class="col-12 bg-light py-3">
                                            <select class="form-control" id="docente" v-model="promotion_potition"
                                                :disabled="dte.evatype === 0">
                                                <option v-for="item in dte.potitiondataimp" :value="item.position_id">
                                                    {{ item.position_name }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <h5 class="text-center text-light" style="background: #091f40">Aprobaciones</h5>
                            <div class="row mt-1 pb-1 align-items-center" style="background: #d1d7dd">
                                <div class="col-6">
                                    Evaluador
                                    <div class="row mt-1 pb-1 " style="background: #d1d7dd">
                                        <div class="col-12 bg-light py-3">
                                            {{ dte.evaluador.first_name }} {{ dte.evaluador.first_surname }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    Evaluado
                                    <div class="row mt-1 pb-1 " style="background: #d1d7dd">
                                        <div class="col-12 bg-light py-3">
                                            {{ dte.evaluado.first_name }} {{ dte.evaluado.first_surname }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-1 pb-1" style="background: #d1d7dd">
                        <div class="col-12">
                        </div>
                    </div>
                </div>


                <div class="modal-footer" for="dashboard" :hidden="isInfo">
                    <div class="dashboard-form-container-form-button" data-bs-toggle="modal"
                        data-bs-target="#staticBackdrop">
                        <span>Guardar</span>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel" v-if="isEvaluator === false">Enviar
                                    Autoevaluacion</h5>
                                <h5 class="modal-title" id="staticBackdropLabel" v-if="isEvaluator === true">Enviar
                                    Evaluacion</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body" v-if="isEvaluator === false">
                                ADVERTENCIA: Una vez enviado todos los datos no podra editarlos, ¿Seguro que desea
                                enviar los datos?
                            </div>
                            <div class="modal-body" v-if="isEvaluator === true">
                                ADVERTENCIA: ¿Seguro que desea enviar los datos?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" @click="sendForm">Send</button>
                            </div>
                        </div>
                    </div>
                </div>





            </div>

        </div>


    </div>
</template>

<script>

import {
    componentsUI,
    methodsUI,
    watchUI,
    CrudUi,
    dataUI,
} from "../../../UI/UIConfig";
import { forEach } from "lodash";

export default {
    props: {
        dte: {},
        isEvaluator: false,
        isEdit: false,
        isInfo: false,
    },
    data() {
        return {
            alreadyeva: false,
            modalControl: null,
            depa: 3,
            questions: {},
            form: {},
            formtotals: {
                0: { auto: 0, eva: 0 },
                1: { auto: 0, eva: 0 },
                2: { auto: 0, eva: 0 },
                3: { auto: 0, eva: 0 },
                4: { auto: 0, eva: 0 },
                5: { auto: 0, eva: 0 },
            },
            formact: {},
            formactr: {},
            formacttotal: { totalh: 0, averagecc: 0, averagepc: 0 },
            formag: {
                0: '',
                1: '',
                2: '',
                3: '',
                4: '',
                5: '',
            },
            evaluator_comment: '',
            promotion_potition: 0,
            haask: {
                //DEPARTAMENTO BPC//
                planilladep1: {
                    seccion1: {
                        0: 'Empleó usted técnicas de trabajo propias que permitieran que su trabajo se realizara en forma efectiva y eficiente.',
                        1: 'Suministró al personal que está a su cargo las directrices y herramientas necesarias para que el trabajo se realizara en forma efectiva y eficiente.',
                        2: 'Mantuvo presente el objetivo del trabajo y lo relacionó con las conclusiones logradas.',
                        3: 'Realizó la evaluación de los riesgos a los que está expuesto el encargo requerido por el Cliente.',
                        4: 'Diseñó pruebas que permitieran evaluar el riesgo al que está expuesto el encargo requerido por el Cliente.',
                        5: 'Analizó los hallazgos determinados de forma asertiva y oportuna.',
                        6: 'Informó a su supervisor inmediato los hallazgos determinados y las conclusiones alcanzadas.',
                    },
                    seccion2: {
                        0: 'Demostró conocimiento de las normas y regulaciones aplicables en el Cliente.',
                        1: 'Demostró conocimiento en cuanto al marco metodológico de COSO 2013, COSO 2017 ERM, BRASILEA, SOX.',
                        2: 'Evaluó el impacto que produce en los Cientes los cambios generados en el período de acuerdo a las normas y regulaciones aplicables.',
                        3: 'Presentó ante su supervisor inmediato explicaciones adecuadas (basada en fundamentos técnicos) ' +
                            'sobre los aspectos más resaltantes encontrados durante el desarrollo del trabajo de campo.',
                        4: ' Realizó pruebas de cumplimiento y analíticas basadas en la actividad del Cliente y tipo de ' +
                            'operaciones demostrando en las mismas generar las conclusiones acertadas y análisis adecuados de los resultados obtenidos.',
                        5: ' Redactó hallazgos, notas, recomendaciones en los informes cónsonas con las normativas vigentes aplicables al Clientes.',
                        6: 'Elaboró informes de acuerdo con las normativas vigentes aplicables al Cliente y con las normas y procesos etablecidos por la Firma.',
                        7: ' Demostró conocimientos apropiados en la utilización de las herramientas informáticas (Word,Excel,Power Point, entre otros).',
                    },
                    seccion3: {
                        0: 'Realizó la adaptación de las metodologías de trabajo para cada área y a la medida del Cliente.',
                        1: 'Preparó los papeles de trabajo necesarios con evidencia suficiente y competente que sustentaron ' +
                            'las conclusiones alcanzadas, las cuales fueron incluidas en dichos papels y en los informes respectivos',
                        2: 'Referenció e identificó apropiadamente los papeles de trabajo.',
                        3: 'Elaboró papeles de trabajo de acuerdo con las Normas, Plantillas, Índices y demás\n' +
                            'proformas empleadas en la División y a los estándares de calidad de la Firma.',
                        4: 'Revisó que los papeles de trabajo elaborados por el personal a su cargo fueron\n' +
                            'preparados de acuerdo con las Normas establecidas en la División y a los estándares de calidad de la Firma.',
                        5: 'Cumplió con los procedimientos de archivo y resguardo de papeles de trabajo de acuerdo a las políticas de la Firma.',
                    },
                    seccion4: {
                        0: ' Diseñó y aplicó pruebas eficaces que generaron un incremento en su eficiencia y productividad.',
                        1: 'Terminó el trabajo en el tiempo establecido sin disminuir los entándares de calidad de la Firma. En caso de haber excedido el tiempo establecido usted explicó a su supervisor inmediato las razones de dicho exceso.',
                        3: 'Realizó sus trabajos de acuerdo con los requerimientos de sus supervisores en términos de contenido, presentación y estándar de las políticas de calidad de la Divisíon y de la Firma..',
                        4: 'Estuvo usted atento a las fechas claves de entrega de resultados o informes, y colaboró con su entrea oportuna.',
                        5: 'Realizó oportunamente los reportes de tiempo bajo su responsabilidad.',
                        6: 'Revisó oportunamente los reportes de tiempo del personal bajo su supervisión y verificó que las horas allí incluidas fueron productivas.',
                    },
                    seccion5: {
                        0: 'Se interesó en conocer al Cliente, sus expectativas, necesidades y problemas.',
                        1: 'Se mostró cortés y educado en su trato al personal con el Cliente y mantuvo una buena comunicación con el mismo.',
                        3: 'Considera usted que el servicio ofrecido al Cliente, cubrió las expectativas del mismo, lo cual se evidenció en la solicitud efectuada por el Cliente de continuar con nuestra relación profesional(Asesor-Cliente).',
                        4: 'Mantuvo constante comunicación con el Cliente del proyecto de los avances y posibles resultados del engargo(Asesor-Cliente).',
                    },
                    seccion6: {
                        0: 'Se adaptó a las políticas sobre vestimenta y horario, establecidos por la Firma o por el Cliente.',
                        1: 'Se mostró interesado en aportar soluciones o alternativas ante los problemas o inconvenientes que pudiesen surgir en la buena marcha profesional de la División.',
                        3: 'Fue capaz de transmitir en forma adecuada, conocimientos técnicos a sus compañeros de equipo en cuanto a lineamientos generales dictador por la Firma y a temas técnicos relacionados con el Ciente.',
                        4: 'Logró el respeto del personal profesional con el que le correspondió interactuar, tanto de la División como de la Firma y consecuentemente, su mayor colaboración.',
                        5: 'Demuestra seguridad en sí mismo al formalizar sus apreciaciones.',
                        6: '¿Se considera una persona proactiva? Indique ¿por qué? ',
                        7: 'Atendió y acató las observaciones y puntos de mejora que se hacen para el buen desarrollo de sus actividades en los diferentes proyecto designados.',
                        8: 'Mantiene adecuados niveles de comunicación y posee capacidad para vender sus ideas.',
                    },
                },
                //DEPARTAMENTO OFICIAL DE CUMPLIMIENTO//
                planilladep2: {
                    seccion1: {
                        0: 'Empleó usted técnicas de trabajo propias que permitieran que su trabajo se realizara en forma efectiva y eficiente.',
                        1: 'Suministró al personal que está a su cargo las directrices y herramientas necesarias para que el trabajo se realizara en forma efectiva y eficiente.',
                        2: 'Mantuvo presente el objetivo del trabajo y lo relacionó con las conclusiones logradas.',
                        3: 'Realizó la evaluación de los riesgos a los que está expuesto el encargo requerido por el Cliente.',
                        4: 'Diseñó pruebas que permitieron mitigar los riesgos a los que está expuesta la Firma.',
                        5: 'Analizó los hallazgos determinados de forma oportuna.',
                        6: 'Informó de forma oportuna a su supervisor inmdiato los hallazgos determinados y las conclusiones alcanzadas.',
                    },
                    seccion2: {
                        0: 'Demostró conocimiento de las normas y regulaciones aplicables en el Cliente.',
                        1: 'Evaluó el impacto que producen los cambios recientes respecto a las normas y regulaciones en la Firma y su relación con los Clientes.',
                        2: 'Presentó ante su supervisor inmediato explicaciones adecuadas (basadas en fundamentos técnicos) sobre los aspectos más resaltantes' +
                            'encontrados durante el desarrollo de su trabajo.',
                        3: 'Elaboró informes cónsonos con la normativa vigente aplicable a la Firma.',
                        4: 'Demostró conocimientos apropiados en la utilización de las herramientas informáticas (Word, Excel, Power Point, entre otros)',
                    },
                    seccion3: {
                        0: 'Realizó un resguardo de la documentación entregada por el Cliente en físico y digital, relacionada a la Política Conozca a ' +
                            'su Cliente, así como la confidencialidad de la misma.',
                        1: 'Preparó evidencias suficientes y competentes que sustentaron las conclusiones alcanzadas, respecto al análisis y validación' +
                            'de datos de Clientes.',
                        2: 'Identificó apropiadamente los documentos suministrados por el Cliente.',
                        3: 'Revisó que los documentos identificados por el personal a su cargo, fueron preparados de acuerdo a los estándares de calidad ' +
                            'de la Firma.',
                        4: 'Cumplió con los procedimientos de archivos y resguardo de los documentos de acuerdo a las políticas de la Firma',
                    },
                    seccion4: {
                        0: 'Terminó el trabajo en el tiempo establecido sin disminuir los estándares de calidad de la Firma.' +
                            'En caso de haber excedido el tiempo establecido, explica a su supervisor inmediato las razones de dicho exceso',
                        1: 'Realizó sus trabajos de acuerdo con los requerimientos de sus supervisores en términos de contenidos, presentación y' +
                            'estándar de las políticas de calidad de la Firma.',
                        2: 'Estuvo atento a las fechas claves de los entregables y colabora con su entrega oportuna.',
                        3: 'Revisó oportunamente los reportes de tiempo del personal bajo su supervisión y verificó que las horas allí incluidas' +
                            'fueron productivas.',
                    },
                    seccion5: {
                        0: 'Demostró cortesía y educación en el trato del personal del Cliente y mantuvo una buena comunicación con éste',
                    },
                    seccion6: {
                        0: 'Se adaptó a las políticas sobre vestimenta y horario, establecidos por la Firma o por el Cliente.',
                        1: 'Demostró interés en aportar soluciones o alternativas ante los problemas o inconvenientes que pudiesen seguir en el departamento al cual pertenece.',
                        3: 'Trasmitió de forma adecuada conocimientos técnicos a sus compañeros de equipo en cuanto a lineamientos generales dictador por la Firma.',
                        4: 'Demostró seguridad en sí mismo al formalizar sus apreciaciones.',
                        5: '¿Se considera una persona proactiva? Indique ¿por qué? ',
                        6: 'Mantuvo adecuados niveles de comunicación y posee capacidad para vender sus ideas.',
                    },
                },
                //DEPARTAMENTO OUTSORCING//
                planilladep3: {
                    seccion1: {
                        0: 'Empleó usted técnicas de trabajo propias que permitieran que su trabajo se realizara en forma efectiva y eficiente.',
                        1: 'Suministró al personal que está a su cargo las directrices y herramientas necesarias para que el trabajo se realizara en forma efectiva y eficiente.',
                        2: 'Mantuvo presente el objetivo del trabajo y lo relacionó con las conclusiones logradas.',
                        3: 'Realizó los análisis de los indicadores financieros o patrimoniales que pueden afectar al Cliente considerando el merccado ' +
                            'en el que este se desenvuelve.',
                        4: 'Realizó la evaluación de los riesgos a los que está expuesto el cliente.',
                        5: 'Identificó y determinó las áreas críticas de los estados financieros.',
                        6: 'Analizó los hallazgos determinados de forma oportuna.',
                        7: 'Informó de forma oportuna a su supervisor inmediato los hallazgos determinados y las conclusiones alcanzadas.',
                    },
                    seccion2: {
                        0: 'Demostró conocimiento de las normas y regulaciones aplicables en el Cliente.',
                        1: 'Demostró conocimientos apropiados en la utilización de las herramientas informáticas (Word, Excel, Power Point, entre otros)',
                        2: 'Evaluó el impacto que produce en los Clientes los cambios generados en el periodo de las normas y regulaciones.',
                        3: 'Evaluó el impacto que producen los cambios recientes respecto de las normas y regulaciones impositivas y contables' +
                            'relevantes, en los Clientes.',
                        4: 'Presentó ante su supervisor inmediato explicaciones adecuadas (basada en fundamentos técnicos) sobre los aspectos' +
                            'más resaltantes encontrados durante el desarrollo de su trabajo.',
                        5: 'Elaboró notas de informes cónsonas con la normativa vigente aplicable al Cliente.',
                        6: 'Elaboró informes de acuerdo con la normativa vigente aplicable al Cliente y con las normas y procesos establecidos ' +
                            'por la Firma'
                    },
                    seccion3: {
                        0: 'Preparó el respaldo y la documentación necesaria con evidencia suficiente y competente que ' +
                            'sustentaron las conclusiones alcanzadas, las cuales fueron incluidas en dichos documentos.',
                        1: 'Referenció e identificó apropiadamente la documentación (en este punto hacer referencia a como estructura y respalda la' +
                            'información de los clientes).',
                        2: 'Revisó que los documentos identificados por el personal a su cargo, fueron preparados de acuerdo a los estándares de calidad ' +
                            'de la Firma.',
                        3: 'Cumplió con los procedimientos de archivos y resguardo de los documentos de acuerdo a las políticas de la Firma',
                    },
                    seccion4: {
                        0: 'Terminó el trabajo en el tiempo establecido sin disminuir los estándares de calidad de la Firma.' +
                            'En caso de haber excedido el tiempo establecido, explica a su supervisor inmediato las razones de dicho exceso',
                        1: 'Realizó sus trabajos de acuerdo con los requerimientos de sus supervisores en términos de contenidos, presentación y' +
                            'estándar de las políticas de calidad de la Firma.',
                        2: 'Estuvo atento a las fechas claves de entrega de informes y colaboró con su entrega oportuna.',
                        3: 'Revisó oportunamente los reportes de tiempo del personal bajo su supervisión y verificó que las horas allí incluidas' +
                            'fueron productivas.',
                    },
                    seccion5: {
                        0: 'Se interesó en conocer al cliente y sus problemas',
                        1: 'Se mostró cortés y educado en su trato al personal del Cliente y mantuvo una buena comunicación con el mismo',
                        2: 'Considera usted que el servicio ofrecido al cliente cubrió las expectativas del mismo, lo cual se evidenció ' +
                            'en la solicitud efectuada por el cliente de continuar con nuestra relación profesional (Contador-Cliente)',
                    },
                    seccion6: {
                        0: 'Se adaptó a las políticas sobre vestimenta y horario, establecidos por la Firma o por el Cliente.',
                        1: 'Demostró interés en aportar soluciones o alternativas ante los problemas o inconvenientes que pudiesen seguir en el departamento al cual pertenece.',
                        2: 'Trasmitió de forma adecuada conocimientos técnicos a sus compañeros de equipo en cuanto a lineamientos generales dictador por la Firma y a ' +
                            'temas técnicos relacionados con el cliente.',
                        3: 'Logró el respeto del personal y consecuentemente su mayor colaboración.',
                        4: 'Demuestra seguridad en sí mismo al formalizar sus apreciaciones.',
                        5: '¿Se considera una persona proactiva? Indique ¿por qué? ',
                        6: 'Mantiene adecuados niveles de comunicación y posee capacidad para vender sus ideas.',
                    },
                },
                //AUDITORIA FINANCIERA//
                planilladep4: {
                    seccion1: {
                        0: 'Empleó usted técnicas de trabajo propias que permitieran que su trabajo se realizara en forma efectiva y eficiente.',
                        1: 'Suministró al personal que está a su cargo las directrices y herramientas necesarias para que el trabajo se realizara en forma efectiva y eficiente.',
                        2: 'Mantuvo presente el objetivo del trabajo y lo relacionó con las conclusiones logradas.',
                        3: 'Realizó los análisis correspondientes a las variaciones más significativas determinadas en la revisión' +
                            'analítica de los estados financieros y del área asignada.',
                        4: 'Realizó los análisis de los indicadores financieros o patrimoniales que puedan afectar al Cliente' +
                            'considerando el mercado en el que este se desenvuelve',
                        5: 'Realizó la evaluación de los riesgos a los que está expuesto el cliente.',
                        6: 'Identificó y determinó las áreas críticas de los estados financieros.',
                        7: 'Diseñó pruebas de auditoría que permitieron mitigar los riesgos a los que esta expuesto el Cliente.',
                        8: 'Analizó los hallazgos determinados de forma oportuna.',
                        9: 'Informó de forma oportuna a su supervisor inmediato los hallazgos determinados y las conclusiones alcanzadas.',
                    },
                    seccion2: {
                        0: 'Demostró conocimiento de las normas y regulaciones aplicables en el Cliente.',
                        1: 'Evaluó el impacto que produce en los Clientes los cambios generados en el periodo de las normas y regulaciones.',
                        2: 'Evaluó el impacto que producen los cambios recientes respecto de las normas y regulaciones impositivas y contables' +
                            'relevantes, en los Clientes.',
                        3: 'Presentó ante su supervisor inmediato explicaciones adecuadas (basada en fundamentos técnicos) sobre los aspectos' +
                            'más resaltantes encontrados durante el desarrollo de su trabajo.',
                        4: 'Realizó pruebas de auditoria sustantivas, de cumplimiento y analíticas basado en los niveles de materialidad del Cliente,' +
                            'demostrando las mismas conclusiones y análisis de los resultados obtenidos.',
                        5: 'Elaboró notas de informes cónsonas con la normativa vigente aplicable al Cliente.',
                        6: 'Elaboró informes de acuerdo con la normativa vigente aplicable al Cliente y con las normas y procesos establecidos ' +
                            'por la Firma',
                        7: 'Demostró conocimientos apropiados en la utilización de las herramientas informáticas (Word, Excel, Power Point, entre otros)',
                    },
                    seccion3: {
                        0: 'Realizó la adaptación de los programas de trabajo para cada área a la medida del Cliente',
                        1: 'Preparó los papeles de trabajo necesarios con evidencia suficiente y competente que ' +
                            'sustentaron las conclusiones alcanzadas, las cuales fueron incluidas en dichos papeles.',
                        2: 'Referenció e identificó apropiadamente los papeles de trabajo.',
                        3: 'Elaboró papeles de trabajo de acuerdo con las Normas de Auditoría y a los estándares de calidad de la Firma.',
                        4: 'Revisó que los papeles de trabajo elaborados por el personal a su cargo, fueron preparados de acuerdo a las Normas de ' +
                            'Auditoría y a los estándares de calidad de la Firma',
                        5: 'Cumplió con los procedimientos de archivos y resguardo de papeles de trabajo de acuerdo a las políticas de la Firma',
                    },
                    seccion4: {
                        0: 'Diseñó y aplicó pruebas de auditoria eficaces que generaron un incremento en su eficiencia y productividad',
                        1: 'Terminó el trabajo en el tiempo establecido sin disminuir los estándares de calidad de la Firma.' +
                            'En caso de haber excedido el tiempo establecido, explica a su supervisor inmediato las razones de dicho exceso',
                        2: 'Realizó sus trabajos de acuerdo con los requerimientos de sus supervisores en términos de contenidos, presentación y' +
                            'estándar de las políticas de calidad de la Firma.',
                        3: 'Estuvo atento a las fechas claves de entrega de informes y colaboró con su entrega oportuna.',
                        4: 'Revisó oportunamente los reportes de tiempo del personal bajo su supervisión y verificó que las horas allí incluidas' +
                            'fueron productivas.',
                    },
                    seccion5: {
                        0: 'Se interesó en conocer al cliente y sus problemas',
                        1: 'Se mostró cortés y educado en su trato al personal del Cliente y mantuvo una buena comunicación con el mismo',
                        2: 'Considera usted que el servicio ofrecido al cliente cubrió las expectativas del mismo, lo cual se evidenció ' +
                            'en la solicitud efectuada por el Cliente de continuar con nuestra relación profesional (Auditor-Cliente)',
                    },
                    seccion6: {
                        0: 'Se adaptó a las políticas sobre vestimenta, establecidos por la Firma.',
                        1: 'Se adaptó a las políticas de horario establecidos por la Firma o por el Cliente.',
                        2: 'Se mostró interesado en aportar soluciones o alternativas ante los problemas o inconvenientes que pudiesen seguir en el departamento al cual pertenece.',
                        3: 'Fue capaz de transmitir en forma adecuada, conocimientos técnicos a sus compañeros de equipo en cuanto a lineamientos' +
                            'generales dictados por la Firma y a temas técnicos relacionados con el Cliente',
                        4: 'Fue capaz de trasmitir de forma adecuada conocimientos técnicos a sus compañeros de equipo en cuanto a lineamientos generales dictados por la Firma y a ' +
                            'temas técnicos relacionados con el Cliente.',
                        5: 'Logró el respeto del personal y consecuentemente su mayor colaboración.',
                        6: 'Demuestra seguridad en sí mismo al formalizar sus apreciaciones.',
                        7: 'Se considera proactivo',
                        8: 'Mantiene adecuados niveles de comunicación y posee capacidad para vender sus ideas.',
                    },
                },
                //AUDITORIA TI//
                planilladep5: {
                    seccion1: {
                        0: 'Empleó usted técnicas de trabajo (Entrevista, Observación, Muestreo, Auditoria asistida por ' +
                            'computadora) que permitieran que su trabajo se realizara en forma efectiva y eficiente.',
                        1: 'Suministró al personal que está a su cargo las directrices y herramientas necesarias para que el trabajo se realizara en forma efectiva y eficiente.',
                        2: 'Mantuvo presente el objetivo del trabajo y lo relacionó con las conclusiones logradas.',
                        3: 'Realizó los análisis correspondientes a las variaciones más significativas determinadas en la revisión' +
                            'de Tecnología y Seguridad de la Información.',
                        4: 'Realizó los análisis de los indicadores Tecnológicos y de Seguridad de la Información que puedan ' +
                            'afectar al cliente considerando el mercado en el que esta se desenvuelve.',
                        5: 'Realizó las evaluación de los riesgos Tecnológicos a los que esta expuesto al cliente.',
                        6: 'Identificó y determinó las áreas críticas de la plataforma Tecnológica de la organización.',
                        7: 'Diseñó pruebas de auditoría que permitieron mitigar los riesgos a los que esta expuesto el Cliente.',
                        8: 'Analizó los hallazgos determinados de forma oportuna.',
                        9: 'Informó de forma oportuna a su supervisor inmediato los hallazgos determinados y las conclusiones alcanzadas.',
                    },
                    seccion2: {
                        0: 'Demostró conocimiento de las normas (CISA, ISO, ITIL, COBIT) y regulaciones aplicables en el cliente.',
                        1: 'Evaluó el impacto que produce en los Clientes los cambios generados en el periodo de las normas y regulaciones.',
                        2: 'Evalua el impacto que producen los cambios recientes respecto de las normas y regulaciones suministradas ' +
                            'por el ente regulador en los clientes.',
                        3: 'Presentó ante su supervisor inmediato explicaciones adecuadas (basada en fundamentos técnicos) sobre los aspectos' +
                            'más resaltantes encontrados durante el desarrollo de su trabajo.',
                        4: 'Realizó pruebas de auditoria sustantivas, de cumplimiento y analíticas descriptivas; demostrando las mismas' +
                            'conclusiones y análisis de los resultados obtenidos.',
                        5: 'Elaboró notas de informes cónsonas con la normativa vigente aplicable al Cliente.',
                        6: 'Elaboró informes de acuerdo con la normativa vigente aplicable al Cliente y con las normas y procesos establecidos ' +
                            'por la Firma',
                        7: 'Demostró conocimientos apropiados en la utilización de las herramientas Tecnológicas',
                    },
                    seccion3: {
                        0: 'Realizó la adaptación de los programas de trabajo para cada área a la medida del Cliente',
                        1: 'Preparó los papeles de trabajo necesarios con evidencia suficiente y competente que ' +
                            'sustentaron las conclusiones alcanzadas, las cuales fueron incluidas en dichos papeles.',
                        2: 'Referenció e identificó apropiadamente los papeles de trabajo.',
                        3: 'Elaboró papeles de trabajo de acuerdo con las Normas de Auditoría y a los estándares de calidad de la Firma.',
                        4: 'Revisó que los papeles de trabajo elaborados por el personal a su cargo, fueron preparados de acuerdo a las Normas de ' +
                            'Auditoría de Tecnología de Información y los estándares de calidad de la Firma',
                        5: 'Cumplió con los procedimientos de archivos y resguardo de papeles de trabajo de acuerdo a las políticas de la Firma',
                    },
                    seccion4: {
                        0: 'Diseñó y aplicó pruebas de auditoria eficaces que generaron un incremento en su eficiencia y productividad',
                        1: 'Terminó el trabajo en el tiempo establecido sin disminuir los estándares de calidad de la Firma.' +
                            'En caso de haber excedido el tiempo establecido, explica a su supervisor inmediato las razones de dicho exceso',
                        2: 'Realizó sus trabajos de acuerdo con los requerimientos de sus supervisores en términos de contenidos, presentación y' +
                            'estándar de las políticas de calidad de la Firma.',
                        3: 'Estuvo atento a las fechas claves de entrega de informes, y colaboró con su entrega oportuna.',
                        4: 'Revisó oportunamente los reportes de tiempo del personal bajo su supervisión y verificó que las horas allí incluidas' +
                            'fueron productivas.',
                    },
                    seccion5: {
                        0: 'Se interesó en conocer al cliente y sus problemas',
                        1: 'Se mostró cortés y educado en su trato al personal del Cliente y mantuvo una buena comunicación con el mismo',
                        2: 'Considera usted que el servicio ofrecido al cliente cubrió las expectativas del mismo, lo cual se evidenció ' +
                            'en la solicitud efectuada por el Cliente de continuar con nuestra relación profesional (Auditor-Cliente)',
                    },
                    seccion6: {
                        0: 'Se adaptó a las políticas sobre vestimenta, establecidos por la Firma.',
                        1: 'Se adaptó a las políticas de horario establecidos por la Firma o por el Cliente.',
                        2: 'Se mostró interesado en aportar soluciones o alternativas ante los problemas o inconvenientes que pudiesen seguir en el departamento al cual pertenece.',
                        3: 'Fue capaz de transmitir en forma adecuada, conocimientos técnicos a sus compañeros de equipo en cuanto a lineamientos' +
                            'generales dictados por la Firma y a temas técnicos relacionados con el Cliente',
                        4: 'Usted considera que transmite y asume la misión, visión y los valores de la Organización.',
                        5: 'Logró el respeto del personal y consecuentemente su mayor colaboración.',
                        6: 'Demuestra seguridad en sí mismo al formalizar sus apreciaciones.',
                        7: 'Se considera proactivo',
                        8: 'Mantiene adecuados niveles de comunicación y posee capacidad para vender sus ideas.',
                    },
                },
                //CAPITAL HUMANO//
                planilladep6: {
                    seccion1: {
                        0: 'Empleó usted técnicas de trabajo propias que permitieran que su trabaj se realizará en forma efectiva y eficiente.',
                        1: 'Suministro al personal que está a su cargo las directrices y herramientas necesarias para que el trabajo se realizara en forma efectiva y eficiente.',
                        3: 'Mantuvo presente el objetivo del trabajo y lo relacionó con las conclusiones logradas.',
                        4: 'Realizó los análisis correspondientes a las funciones de Nómina, Beneficios Contactuales y Entes Gubernamentales.',
                        5: 'Realizó los análisis de los indicadores laborales que pueden afectar al cliente de Outsorcing RRHH, considerando ' +
                            'el mercado en el que este se desenvuelve.',
                        6: 'Realizó las evaluación de los riesgos a los que esta expuesto al cliente del area de Outsourcing Nómina.',
                        7: 'Identificó y determinó las áreas críticas del Cliente de Outsourcing de RRHH.',
                        8: 'Diseñó pruebas en el area de Recursos Humanos que permitieron mitigar los riesgos del area laboral, a los que esta expuesto el cliente.',
                        9: 'Analizó los hallazgos determinados en los Clientes de Outsourcing de RRHH de forma oportuna.',
                        10: 'Informó de forma oportuna a su supervisor inmediato los hallazgos determinados y las conclusiones alcanzadas.',
                    },
                    seccion2: {
                        0: 'Demostró conocimiento de las Leyes y Normas Laborales.',
                        1: 'Evalúa el impacto que produce en los Clientes los cambios a nivel laboral.',
                        2: 'Evalua el cumplimiento de las Normas y Leyes Laborales por parte de los clientes.',
                        3: 'Presentó ante su supervisor inmediato explicaciones adecuadas (basada en fundamentos técnicos) sobre los aspectos' +
                            'más resaltantes encontrados durante el desarrollo de su trabajo.',
                        4: 'Realizó pruebas, de cumplimiento y analíticas basado en los niveles de materialidad del cliente;' +
                            'demostrando las mismas conclusiones y análisis de los resultados obtenidos de los Outsourcing' +
                            'de RRHH y Nómina.',
                        5: 'Elaboró notas de informes apegados a las Normas y Leyes Laborales.',
                        6: 'Elaboró informes de acuerdo con la normativa y leyes laborales vigente aplicable al cliente.',
                        7: 'Demostró conocimientos apropiados en la utilización de las herramientas informáticas (Word, Excel, Power Point, Sistemas de Nóminas, ' +
                            'Portales de Entes Gubernamentales)',
                    },
                    seccion3: {
                        0: 'Realizó la adaptación de los programas de trabajo para el área de Recursos Humanos a la medida del cliente',
                        1: 'Preparó los papeles de trabajo necesarios con evidencia suficiente y competente que ' +
                            'sustentaron las conclusiones alcanzadas, las cuales fueron incluidas en dichos papeles de los clientes de Outsourcing Nómina.',
                        2: 'Referenció e identificó apropiadamente los papeles de trabajo.',
                        3: 'Elaboró papeles de trabajo de acuerdo con las Normas y Leyes Laborales.',
                        4: 'Revisó que los papeles de trabajo elaborados por el personal a su cargo, fueron preparados de acuerdo con ' +
                            'las Normas y Leyes Laborales',
                        5: 'Cumplió con los procedimientos de archivos y resguardo de papeles de trabajo de acuerdo a las políticas de la Firma',
                    },
                    seccion4: {
                        0: 'Diseñó y aplicó pruebas del area de Recursos Humanos eficaces que generaron un incremento en su eficiencia y productividad.',
                        1: 'Terminó el trabajo en el tiempo establecido sin disminuir los estándares de calidad de la Firma.' +
                            'En caso de haber excedido el tiempo establecido, explica a su supervisor inmediato las razones de dicho exceso',
                        2: 'Realizó sus trabajos de acuerdo con los requerimientos de sus supervisores en términos de contenidos, presentación y' +
                            'estándar de las políticas de calidad de la Firma.',
                        3: 'Estuvo atento a las fechas claves de entrega de informes, y colaboró con su entrega oportuna.',
                        4: 'Revisó oportunamente los reportes de tiempo del personal bajo su supervisión y verificó que las horas allí incluidas' +
                            'fueron productivas.',
                    },
                    seccion5: {
                        0: 'Se interesó en conocer al cliente interno (Colaboradores) y a los clientes externos y sus problemas',
                        1: 'Se mostró cortés y educado en su trato al personal del Cliente y mantuvo una buena comunicación con el mismo',
                        2: 'Considera usted que el servicio ofrecido al cliente cubrió las expectativas del mismo, lo cual se evidenció ' +
                            'en la solicitud efectuada por el Cliente de continuar con nuestra relación profesional (RRHH-Cliente)',
                    },
                    seccion6: {
                        0: 'Se adaptó a las políticas sobre vestimenta, establecidos por la Firma.',
                        1: 'Se adaptó a las políticas de horario establecidos por la Firma o por el Cliente.',
                        2: 'Se mostró interesado en aportar soluciones o alternativas ante los problemas o inconvenientes que pudiesen seguir en el departamento al cual pertenece.',
                        3: 'Fue capaz de transmitir en forma adecuada, conocimientos técnicos a sus compañeros de equipo en cuanto a lineamientos' +
                            'generales en materia Laboral',
                        4: 'Usted considera que transmite y asume la misión, visión y los valores de la Organización.',
                        5: 'Logró el respeto del personal y consecuentemente su mayor colaboración.',
                        6: 'Demuestra seguridad en sí mismo al formalizar sus apreciaciones.',
                        7: 'Se considera proactivo',
                        8: 'Mantiene adecuados niveles de comunicación y posee capacidad para vender sus ideas.',
                    },
                },
                //DIVISIÓN TRIBUTARIA Y LEGAL//
                planilladep7: {
                    seccion1: {
                        0: 'Empleó usted técnicas de trabajo propias que permitieran que su trabajo se realizara en forma efectiva y eficiente.',
                        1: 'Suministró al personal que está a su cargo las directrices y herramientas necesarias para que el trabajo se realizara en forma efectiva y eficiente.',
                        2: 'Mantuvo presente el objetivo del trabajo y lo relacionó con las conclusiones logradas.',
                        3: 'Realizó los análisis correspondientes a las variaciones más significativas determinadas en la revisión' +
                            'analítica de la información que le fue proporcionada para lograr el objetivo perseguido con la asignación.',
                        4: 'Realizó la evaluación de los riesgos a los que está expuesto el cliente en materia tributaria.',
                        5: 'Identificó y determinó las áreas de los estados financieros necesarias para el cálculo de las obligaciones tributarias ' +
                            'o revisiones que le fueron encomendadas.',
                        6: 'Diseñó pruebas en materia tributaria que permitieran evaluar o mitigar el riesgo al que está expuesto el cliente.',
                        7: 'Analizó los hallazgos determinados de forma oportuna.',
                        8: 'Informó de forma oportuna a su supervisor inmediato los hallazgos determinados y las conclusiones alcanzadas.',
                    },
                    seccion2: {
                        0: 'Demostró conocimiento de las normas y regulaciones aplicables en el Cliente.',
                        1: 'Evaluó el impacto que produce en los Clientes los cambios generados en el periodo de las normas y regulaciones.',
                        2: 'Evaluó el impacto que producen los cambios recientes respecto de las normas y regulaciones impositivas y contables' +
                            'relevantes, en los Clientes.',
                        3: 'Presentó ante su supervisor inmediato explicaciones adecuadas (basada en fundamentos técnicos) sobre los aspectos' +
                            'más resaltantes encontrados durante el desarrollo de su trabajo.',
                        4: 'Realizó pruebas de cumplimiento en materia tributaria y análisis basado en la actividad del Cliente,' +
                            'y tipo de operaciones demostrando en las mismas generar las conclusiones acertadas y análisis adecuados de los resultados obtenidos.',
                        5: 'Elaboró notas de informes cónsonas con la normativa vigente aplicable al Cliente.',
                        6: 'Elaboró informes de acuerdo con la normativa vigente aplicable al Cliente y con las normas y procesos establecidos ' +
                            'por la Firma',
                        7: 'Demostró conocimientos apropiados en la utilización de las herramientas informáticas (Word, Excel, Power Point, entre otros)',
                    },
                    seccion3: {
                        0: 'Realizó la adaptación de los programas de trabajo para cada área a la medida del Cliente',
                        1: 'Preparó los papeles de trabajo necesarios con evidencia suficiente y competente que ' +
                            'sustentaron las conclusiones alcanzadas, las cuales fueron incluidas en dichos papeles.',
                        2: 'Referenció e identificó apropiadamente los papeles de trabajo.',
                        3: 'Elaboró papeles de trabajo de acuerdo con las Normas, Plantillas, Índices y demás proformes empleadas en la División ' +
                            'y a los estándares de calidad de la Firma.',
                        4: 'Revisó que los papeles de trabajo elaborados por el personal a su cargo, fueron preparados de acuerdo con las Normas ' +
                            'establecidas en la División y a los estándares de calidad de la Firma',
                        5: 'Cumplió con los procedimientos de archivos y resguardo de papeles de trabajo de acuerdo a las políticas de la Firma',
                    },
                    seccion4: {
                        0: 'Diseñó y aplicó pruebas de auditoria eficaces que generaron un incremento en su eficiencia y productividad',
                        1: 'Terminó el trabajo en el tiempo establecido sin disminuir los estándares de calidad de la Firma.' +
                            'En caso de haber excedido el tiempo establecido usted explicó a su supervisor inmediato las razones de dicho exceso',
                        2: 'Realizó sus trabajos de acuerdo con los requerimientos de sus supervisores en términos de contenidos, presentación y' +
                            'estándar de las políticas de calidad de la Firma.',
                        3: 'Estuvo atento a las fechas claves de entrega de informes y colaboró con su entrega oportuna.',
                        4: 'Revisó oportunamente los reportes de tiempo del personal bajo su supervisión y verificó que las horas allí incluidas' +
                            'fueron productivas.',
                    },
                    seccion5: {
                        0: 'Se interesó en conocer al cliente y sus problemas',
                        1: 'Se mostró cortés y educado en su trato al personal del Cliente y mantuvo una buena comunicación con el mismo',
                        2: 'Considera usted que el servicio ofrecido al cliente cubrió las expectativas del mismo, lo cual se evidenció ' +
                            'en la solicitud efectuada por el Cliente de continuar con nuestra relación profesional (Asesor-Cliente)',
                        3: 'Mantiene constante comunicación con el Cliente del proyecto de los avances y posibles resultados del encargo' +
                            '(Asesor-Cliente)',
                    },
                    seccion6: {
                        0: 'Empleó el tiempo disponible en la oficina a actividades que le permitieran mejorar su nivel de conocimiento técnico o ' +
                            'coadyuvasen a mejorar la práctica profesional de la División',
                        1: 'Se adaptó a las políticas sobre vestimenta, establecidos por la Firma.',
                        2: 'Se adaptó a las políticas de horario establecidos por la Firma o por el Cliente.',
                        3: 'Se mostró interesado en aportar soluciones o alternativas ante los problemas o inconvenientes que pudiesen seguir en la buena marcha profesional de la División.',
                        4: 'Fue capaz de transmitir en forma adecuada, conocimientos técnicos a sus compañeros de equipo en cuanto a lineamientos' +
                            'generales dictados por la Firma y a temas técnicos relacionados con el Cliente',
                        5: 'Usted considera que transmite y asume la misión, visión, y los valores de la Organización.',
                        6: 'Logró el respeto del personal profesional con el que le correspondió interactuar, tanto de la División como de la Firma ' +
                            'y consecuentemente, su mayor colaboración.',
                        7: 'Demuestra seguridad en sí mismo al formalizar sus apreciaciones.',
                        8: 'Se considera proactivo. Solicita la asignación de trabajos a realizar, una vez que se encuentra disponible por haber concluido alguno',
                        9: 'Atiende y acata las observaciones que se hacen en la División para el buen uso de los equipos existentes en la oficina, incluido el apoyo' +
                            'tecnológico, así como también de los que le han sido asignados como herramienta de trabajo personal.',
                        10: 'Mantiene adecuados niveles de comunicación y posee capacidad para vender sus ideas.',
                    },
                },
                //ASISTENTE LEGAL III//
                planilladep8: {
                    seccion1: {
                        0: 'Empleó técnicas de trabajo propias que permitieran que su trabajo se realizara en forma efectiva y eficiente.',
                        1: 'Manejó información delicada o confidencial de forma efectiva y eficiente.',
                        2: 'Mantuvo presente el objetivo del trabajo.',
                    },
                    seccion2: {
                        0: 'Demostró conocimiento de las normas y regulaciones aplicables a la Firma.',
                        1: 'Presentó ante su supervisor inmediato explicaciones adecuadas (basadas en fundamentos técnicos) sobre los aspectos ' +
                            'más resalantes encntradas durante el desarrollo de su trabajo.',
                        2: 'Demostró conocimientos apropiados en la utilización de las herramientas informáticas (Word, Excel, Power Point, entre otros)',
                    },
                    seccion3: {
                        0: 'Realizó un resguardo de la documentación entregada por el Cliente en fisico y digital, así como la confidencialidad ' +
                            'de la misma',
                        1: 'Identicó apropiadamente los documentos sumistrados por el Cliente.',
                        2: 'Cumplió con los procedimientos de archivos y resguardo de los documentos de acuerdo a las políticas de la Firma.',
                    },
                    seccion4: {
                        0: 'Terminó el trabajo en el tiempo establecido sin disminuir los estándares de calidad de la Firma.' +
                            'En caso de haber excedido el tiempo establecido, explica a su supervisor inmediato las razones de dicho exceso',
                        1: 'Realizó sus trabajos de acuerdo con los requerimientos de sus supervisores en términos de contenidos, presentación y' +
                            'estándar de las políticas de calidad de la Firma.',
                        2: 'Estuvo atento a las fechas claves de entrega de informes y colaboró con su entrega oportuna.',
                        3: 'Realizó oportunamente los reportes de tiempo.',
                    },
                    seccion5: {
                        0: 'Demostró cortesia y educación en el trato al personal del Cliente y mantiene una buena comunicación con éste',
                    },
                    seccion6: {
                        0: 'Se adaptó a las políticas de sobre vestimenta y horario; establecidos por la Firma.',
                        1: 'Demostró interés en aportar soluciones o alternativas antes los problemas o inconvenientes que pudiesen surgir en el departamento ' +
                            'al cual pertenece.',
                        2: 'Logró el respeto del personal y consecuentemente su mayor colaboración.',
                        3: 'Demuestró seguridad en sí mismo al formalizar sus apreciaciones.',
                        4: '¿Se considera una persona proactiva? Indique ¿por qué? ',
                        5: 'Mantuvo adecuados niveles de comunicación.',
                    },
                },

            },
        };
    },
    mounted() {
        switch (this.dte.department) {
            case 11:
                console.log('oficinal de cumplimiento id - 11')
                this.questions = this.haask.planilladep2
                this.settoform(this.questions)
                break;
            case 2:
                console.log('division triburatia y legal y auditoria financiera id - 2 y 17')
                this.questions = this.haask.planilladep7
                this.settoform(this.questions)
                break;
            case 3:
                console.log('auditoria ti id - 3')
                this.questions = this.haask.planilladep5
                this.settoform(this.questions)
                break;
            case 4:
                console.log('outsorcing id - 4')
                this.questions = this.haask.planilladep3
                this.settoform(this.questions)
                break;
            case 5:
                console.log('auditoria financiera id - ?')
                break;
            case 6:
                console.log('bpc id - 6')
                this.questions = this.haask.planilladep1
                this.settoform(this.questions)
                break;
            case 7:
                console.log('capital humano id - 7')
                this.questions = this.haask.planilladep6
                this.settoform(this.questions)
                break;
            case 8:
                console.log('asistente legal III id - ?')
                break;
            case 17:
                console.log('division triburatia y legal id - 2 y 17')
                this.questions = this.haask.planilladep7
                this.settoform(this.questions)
                break;
            case 999999999:
                console.log('already eva')
                this.alreadyeva = true
                break;
        }
    },
    computed: {

    },
    methods: {
        /**
         * Metodo que envia el form
         * @param {*} dataParams Recibe la data que proviene de formulario
         */
        sendForm() {
            console.log('send form')
            let paramsToPost = {
                data: {
                    tbdetails: {
                        section1: this.form,
                        section1_totals: this.formtotals,
                        section2: {
                            formact: this.formact,
                            formactr: this.formactr
                        },
                        section2_totals: this.formacttotal,
                        section3: this.formag,
                        evaluator_comments: this.evaluator_comment,
                        type_format: this.typedepart,
                    },
                    promotion_p: this.promotion_potition,
                    evaluator: this.dte.evaluador,
                    evaluated: this.dte.evaluado,
                    clousure_control: this.dte.clousure_control,
                    type_format: this.dte.department
                },
                isEdit: this.isEdit,
                evaluator: this.isEvaluator,
            };
            if (this.isEvaluator === true) {
                paramsToPost.updateids = {
                    edu: this.dte.edu,
                    e: this.dte.e,
                    ep: this.dte.ep
                };
                let routesSelfDTO = {
                    post: "controlData",
                    redirect: "/evaluaciones/listado-del-personal",
                    self: this,
                };
                CrudUi.controlCrud(routesSelfDTO, paramsToPost);
            } else if (this.isEvaluator === false) {
                let routesSelfDTO = {
                    post: "controlData",
                    redirect: "/evaluaciones/proyecto-para-evaluar",
                    self: this,
                };
                CrudUi.controlCrud(routesSelfDTO, paramsToPost);
            }

        },
        settoform(items) {
            if (this.isEdit === true) {
                console.log(JSON.parse(this.dte.fulldata.dt_section3))
                let dtact = JSON.parse(this.dte.fulldata.dt_section2)
                this.form = JSON.parse(this.dte.fulldata.dt_section1)
                this.formtotals = JSON.parse(this.dte.fulldata.dt_section1_total)
                this.formact = dtact.formact
                this.formactr = dtact.formactr
                this.formacttotal = JSON.parse(this.dte.fulldata.dt_section2_total)
                this.formag = JSON.parse(this.dte.fulldata.dt_section3)
                this.evaluator_comment = this.dte.fulldata.evaluator_comments
                this.promotion_potition = this.dte.potitionprev.position_propouse ? this.dte.potitionprev.position_propouse : 0
            } else if (this.isEdit === false) {
                let dataKeys = Object.values(items)
                this.form = dataKeys.map((itemValue, key) => {
                    let dataitemvalue = Object.values(itemValue)
                    var ft = dataitemvalue.map(x => {
                        let dtarray = { auto: 0, eva: 0, come: '' }
                        return dtarray
                    })
                    return ft
                })
                this.formact = this.dte.dtactividades.map(x => {
                    x.act = ''
                    this.formacttotal.totalh += parseInt(x.register_hour)
                    return x
                })
                this.formactr = this.dte.dtactividadesr
            }
        },
        averagetotal(x, type) {
            let accu = 0,
                accudiv = 0,
                total = 0;
            switch (type) {
                case 0:
                    this.form[x].forEach(num => {
                        accu += num.auto;
                        if (num.auto > 0) {
                            accudiv += 1;
                        }
                    })
                    if (accudiv === 0) {
                        accudiv = 1;
                    }
                    total = accu / accudiv
                    this.formtotals[x].auto = total.toFixed(2)
                    break;
                case 1:
                    this.form[x].forEach(num => {
                        accu += num.eva;
                        if (num.eva > 0) {
                            accudiv += 1;
                        }
                    })
                    if (accudiv === 0) {
                        accudiv = 1;
                    }
                    total = accu / accudiv
                    this.formtotals[x].eva = total.toFixed(2)
                    break;
            }
        },
        onKeyup(event) {
            // Obtener el valor del input
            const value = event.target.value;
            // Validar que el valor sea un número del 0 al 6
            if (!/^[0-6]+$/.test(value)) {
                // Impedir que se ingrese el valor
                event.target.value = "";
            } else if (value.length > 1) {
                // Impedir que se ingrese más de un dígito
                event.target.value = value.slice(0, 1);
            }
        },
        // Cambiar el post de cliente por usuario
        NextPage(idClient) {
            const paramsDTO = { codigoSQL: idClient };
            const routesDTO = {
                post: "/clientes/update/loadingClient",
                redirect: "/evaluaciones/planilla-dos",
            };
            //Llamamos al método Static que hace la consulta Axios
            CrudUi.enableEdit(routesDTO, paramsDTO);
        },
    },
    mixins: [componentsUI, methodsUI, watchUI, dataUI],

};
// Función de validación personalizada

</script>
