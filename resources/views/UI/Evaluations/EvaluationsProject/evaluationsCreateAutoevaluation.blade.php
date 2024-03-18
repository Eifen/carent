<div id="create-autoevaluation">
    <loading :active="!isMounted"></loading>
    <Evaluation-form v-if="isMounted" :dte="updateModel" :is-edit="false" :is-evaluator="false"
        :is-info="false" @return-view="redirectView('/evaluaciones/periodos')"
        @submit-form="newPeriod"></Evaluation-form>
</div>
