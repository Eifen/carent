<div id="update-evaluation-period">
    <loading :active="!isMounted"></loading>
    <form-period v-if="isMounted" :is-click="isClick" @return-view="redirectView('/evaluaciones/periodos')"
                 @submit-form="updatePeriod" is-edit :data-edit="proxyToJson(updateModel)" ></form-period>
</div>
