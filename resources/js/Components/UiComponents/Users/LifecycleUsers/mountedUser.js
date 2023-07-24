/**
 * Mounted para el componente Users
 * @param {*} self Obtiene la data del componente FormUsers
 */
export const mountedMixin = (self) => {
    self.$emit("encrypt");
};
