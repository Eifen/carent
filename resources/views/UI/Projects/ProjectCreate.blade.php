<div id="create-project">
    <loading :active="!isMounted"></loading>
    <form-projects v-if="isMounted"
    :is-click="isClick"
    @return-view="redirectView('/projects')"
    {{-- @submit-form="newClient"--}}></form-projects>
</div>