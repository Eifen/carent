<div id="close-project">
    {{-- La informacion de los props, los eventos y los componentes cargan simultaneamente --}}
    {{-- load view es el evento y se configura en el closeIndex a traves de un metodo --}}
    {{-- Necesitamos transformar a un formato que lea el js y por eso lo pasamos a un json --}}
    {{-- return es el nombre personalizado de mi evento --}}
    <loading :active="!isMounted"></loading>
    <close-project v-if="isMounted" :load-initial="proxyToJson(updateModel)" active @return="redirectView('/projects')"
        :view-button="isClick" @close-project="closeProject">
    </close-project>
</div>
