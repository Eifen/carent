<div id="evaluator-autoevaluation">
    <loading :active="!isMounted"></loading>
    <Evaluation-form v-if="isMounted" :is-click="true" :dte="updateModel" :is-edit="true" :is-evaluator="true"
                     :is-info="false" @return-view="redirectView('/evaluaciones/periodos')" ></Evaluation-form>
</div>
