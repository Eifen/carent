<div class="dashboard-users" id="section-users">
    <loading :active="!isMounted"></loading>
    <listing-crud :table-info="dataParse()" 
    :pagination-lenght="maxLengthPagination"
    :pagination-limit="lengthColumns"
    :title-object="titleParse()"
    :select-search="searchParse()"
    title-table="usuarios"
    button-title="usuario"
    v-if="isMounted"></listing-crud>
</div>
