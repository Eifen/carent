/**
 * Hook created para formulario de proyectos
 * @param {*} self Herda la data del FormProjects
 */
export const createdMixin = (self) => {
    //Traer a data a traves de solicitud POST
    axios.post('/projects/get-params-inits')
    .then(request => {
        //Asignamos a los select correspondientes
        self.dataSelect.currencies = request.data.currencies;
        self.dataSelect.companies = request.data.companies;
        self.dataSelect.departments = request.data.departments;
        self.dataSelect.status = request.data.status;
        self.dataSelect.clients = request.data.clients;
        self.dataSelect.partners = request.data.partners;
        self.dataSelect.managers = request.data.managers;
    })
    .catch(error => { console.error(error) })
}