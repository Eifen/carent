/**
 * Metodo mounted para el formulario de evaluaciones
 * @param {*} self Hereda la data() del padre
 */
export const mountedMixin = (self) => {
    //Llamamos al metodo que se encarga de activar los watchers
    self.activateWatchers(self.inputWatchers);
};
