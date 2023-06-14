<div id="create-project">
    <loading :active="!isMounted"></loading>
    <form-projects v-if="isMounted"
    :is-click="isClick"
    @return-view="redirectView('/projects')"
    @submit-form="newProject"></form-projects>
</div>