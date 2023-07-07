<div id="section-projects">
    <loading :active="!isMounted"></loading>
    <listing-crud  v-if="isMounted"
    :title-object="proxyToJson(projectsColumns)"
    :pagination-lenght="maxLengthPagination"
    :pagination-limit="lengthColumns"
    :table-info="proxyToJson(listData)"
    title-table="proyectos"
    button-title="proyecto"
    :select-search="proxyToJson(selectSearch)"
    not-found-message="No existen proyectos creados"
    view-create
    view-search
    @createButton="createProject()"
    @columns1target="editProject"></listing-crud>
</div>
