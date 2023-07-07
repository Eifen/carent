/**
 * Metodo mounted para el formulario de proyectos
 * @param {*} self Hereda la data() del padre
 */
export const mountedMixin = (self) => 
{
    //Activamos los watchers
    self.activateWatchers(self.inputWatchers)
}