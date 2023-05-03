/**
 * Mounted para el componente Users
 * @param {*} self Obtiene la data del componente FormUsers
 */
export const mountedMixin = (self) =>
{
    self.$emit('encrypt');

    //Registramos los watch
    for (let cursorWatch = 0; cursorWatch < self.inputWatchers.length; cursorWatch++) {
        const propiedadesWatchers = self.inputWatchers[cursorWatch].propiedades;

        //Activamos los watch
        for(let cursorPropiedad = 0; cursorPropiedad < propiedadesWatchers.length; cursorPropiedad++)
        {
            const propiedad = propiedadesWatchers[cursorPropiedad]
            self.$watch(propiedad,self.inputWatchers[cursorWatch].watch);
        }
    }
}