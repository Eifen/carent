<div id="update-project">
    <loading :active="!isMounted"></loading>
    <form-projects v-if="isMounted"
    :is-click="isClick"
    is-edit
    :data-edit="updateModel"
    @return-view="redirectView('/projects')"
    @submit-form="updateProject"
    @init-project="prepareUpdate({{ json_encode(Session::get('projectUpdate')) }}, '/projects/delete-update-data')"></form-projects>
</div>
