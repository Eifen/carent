<div id="close-project">
    {{-- La informacion de los props, los eventos y los componentes cargan simultaneamente --}}
    {{-- load view es el evento y se configura en el closeIndex a traves de un metodo --}}
    {{-- Necesitamos transformar a un formato que lea el js y por eso lo pasamos a un json --}}
    <close-project :load-initial="updateModel" :active="isMounted"
        @load-view="prepareUpdate({{ json_encode(Session::get('closeProject')) }}, '/projects/delete-update-data')">
    </close-project>
</div>
