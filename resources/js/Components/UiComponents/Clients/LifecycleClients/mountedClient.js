/**
 * Metodo mounted para el formulario de clientes
 * @param {*} self Hereda la data() del padre
 */
export const mountedMixin = (self) =>
{
    //Registramos los Watch
    for (let cursorWatcher = 0; cursorWatcher < self.inputWatchers.length; cursorWatcher++) {
        const propiedades = self.inputWatchers[cursorWatcher].propiedades;

        //Una vez registrada la fila actual, hacemos un for en su estructura de objeto
        for (let cursorPropiedad = 0; cursorPropiedad < propiedades.length; cursorPropiedad++) {
            const propiedad = propiedades[cursorPropiedad];

            //Una vez capturamos la propiedades, registramos su watcher
            self.$watch(propiedad,self.inputWatchers[cursorWatcher].watch);
        }

    }
}
