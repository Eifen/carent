<div id="update-project">
    <loading :active="!isMounted"></loading>
    <form-projects v-if="isMounted" :is-click="isClick" is-edit :data-edit="proxyToJson(updateModel)"
        @return-view="redirectView('/projects')" @submit-form="updateProject"></form-projects>
</div>
