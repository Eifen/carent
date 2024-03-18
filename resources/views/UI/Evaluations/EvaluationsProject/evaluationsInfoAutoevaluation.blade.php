<div id="info-autoevaluation">
    <loading :active="!isMounted"></loading>
    <Evaluation-form v-if="isMounted" :is-click="true" :dte="updateModel" :is-edit="true" :is-evaluator="false"
                     :is-info="true" @return-view="redirectView('/evaluaciones/periodos')" ></Evaluation-form>
</div>
