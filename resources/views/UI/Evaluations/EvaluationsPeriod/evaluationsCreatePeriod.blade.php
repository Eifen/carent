<div id="create-evaluation-period">
    <loading :active="!isMounted"></loading>
    <form-period v-if="isMounted" :is-click="isClick" @return-view="redirectView('/evaluaciones/periodos')"
        @submit-form="newPeriod"></form-period>
</div>
