<div id='section-reports'>
    <loading :active="listData.length == 0"></loading>
    <reports-index v-if="listData.length != 0" :list-reports="listData"
        :report-permission="{{ json_encode(Session::get('userPermissions')) }}"
        :is-admin="{{ Session::get('isAdmin') }}" :area-id="{{ Session::get('departmentId') }}"
        :user-code="'{{ Session::get('userCode') }}'" :user-position="{{ Session::get('positionId') }}"
        :user-name="'{{ Session::get('userName') }}'"></reports-index>
</div>
