<div id='section-evaluations-report'>
    <loading :active="listData.length == 0"></loading>
    <reports-index v-if="listData.length != 0" :list-reports="listData"
        :report-permission="{{ json_encode(Session::get('userPermissions')) }}"></reports-index>
</div>
