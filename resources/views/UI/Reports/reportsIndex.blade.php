<div id='section-reports'>
    <loading :active="listData.length == 0"></loading>
    <reports-index v-if="listData.length != 0" :list-reports="listData"></reports-index>
</div>
