<div id="billing-control">
    <loading :active="!isMounted"></loading>
    <billing-info v-if="isMounted" :info-project="proxyToJson(updateModel)"></billing-info>
</div>
