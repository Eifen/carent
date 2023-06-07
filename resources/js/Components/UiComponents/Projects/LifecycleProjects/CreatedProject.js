/**
 * Hook created para formulario de proyectos
 * @param {*} self Herda la data del FormProjects
 */
export const createdMixin = (self) => {
    //Traer a data a traves de solicitud POST
    axios.post('/projects/get-params-inits')
    .then(request => {
        //Asignamos a los select correspondientes
        
    })
    .catch(error => { console.error(error) })
}