import { createApp } from "vue/dist/vue.esm-bundler";
import { CrudUi, dataUI, watchUI, componentsUI, methodsUI } from "../UIConfig";
import { globalMethodsReport } from "../../Components/UiComponents/Reports/GlobalReportMethods";

const adminApp = createApp({
    data() {
        return {
            dateStart: "", //Fecha inicial
            dateEnd: "", //Fecha final
            reportColumns: {
                settings: {
                    columnS1: "Acomodar",
                },
                column1: "ID",
                column2: 'Nombre',
                column3: '% Carga total',
                column4: 'Estatus',
            },
            selectSearch: {
                select1: "Nombre",
                select2: "Estatus",
                select3: "Area"
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
            refTotal: 0, //Numero total de horas a cargar
        };
    },
    methods: {
        /**
        * Metodo que registra la fecha en los respectivos cambios
        * @param {String} dateSelect Fecha seleccionada en formato YYY-mm-dd
        * @param {String} type Tipo de fecha, si inicial o final
        */
        dateSearch(dateSelect, type) {
            switch (type) {
                case 'start':
                    this.dateStart = `${dateSelect.year}-${dateSelect.month}-${dateSelect.day}`
                    break;
                case 'end':
                    this.dateEnd = `${dateSelect.year}-${dateSelect.month}-${dateSelect.day}`
                    break;
            }
        },
        /**
        * Muestra las personas que tienen carga 99 a 99.9%
        */
        showReport() {
            //Pasamos como parametro el intervalo de fechas
            axios.post("reports/list-directive-total", { startDate: this.dateStart, endDate: this.dateEnd })
                .then(request => {
                    let requestDTO = [];
                    request.data.message.forEach(user => {
                        user.forEach(period => {
                            requestDTO.push(period);
                        });
                    });
                    let directiveDTO = requestDTO.reduce((acum, intervalData) => {
                        //Creamos una Key
                        const key = intervalData.order_user
                        if (!acum[key]) {
                            acum[key] = {
                                id: intervalData.order_user,
                                nombre: intervalData.nombre,
                                area: intervalData.area,
                                correo: intervalData.correo,
                                nivel: intervalData.nivel,
                                percen_carg: intervalData.percen_carg,
                                proy_hours: parseFloat(intervalData.proy_hours.replace(/\./g, "").replace(/,/, ".")),
                                admin_hours: parseFloat(intervalData.admin_hours.replace(/\./g, "").replace(/,/, ".")),
                                ref_total: parseFloat(intervalData.ref_total.replace(/\./g, "").replace(/,/, ".")),
                                estatus: intervalData.estatus,
                                egreso: intervalData.fecha_egreso,
                                order: intervalData.order,
                                department_order: intervalData.department_order
                            }
                        } else {
                            acum[key].admin_hours += parseFloat(intervalData.admin_hours.replace(/\./g, "").replace(/,/, "."))
                            acum[key].proy_hours += parseFloat(intervalData.proy_hours.replace(/\./g, "").replace(/,/, "."))
                        }
                        return acum;
                    }, {});
                    //Agregamos los porcentajes y el total de horas
                    directiveDTO = Object.values(directiveDTO)
                    //Ordenamos el array
                    directiveDTO.sort(function (a, b) {
                        //Comparamos el orden de cargos
                        let sort = a.department_order - b.department_order
                        if (sort == 0) sort = a.order - b.order;
                        return sort;
                    })
                    directiveDTO.forEach((user) => {
                        const totalHours = user.proy_hours + user.admin_hours;
                        const percenTotal = (totalHours * 100) / (user.ref_total == 0 ? 1 : user.ref_total);
                        //Inservamos el nuevo objeto
                        if (percenTotal >= 99 && percenTotal < 100) {
                            this.directiveList.push({
                                ID: user.id,
                                nombre: user.nombre,
                                "%_tot_hor": this.formatReportNumber(percenTotal),
                                estatus: user.estatus,
                            });
                        }
                    })
                    this.refTotal = request.data.refHour
                    //Acomodamos la longitud minima y su paginacion
                    if (this.directiveList.length < 50) this.directiveLength = this.directiveList.length;
                    this.directivePaginatio = Math.ceil(
                        this.directiveList.length / this.directiveLength
                    );
                    this.isMounted = true
                })
                .catch(error => { console.error(error) })
        }
    },
    computed: {},
    watch: {
        dateStart() {
            //Borramos la fecha inicial
            this.dateEnd = ""
        },
        dateEnd(newDate, oldDate) {
            //Definimos la fecha inicial
            const starDate = this.dateStart.length != 0 ? new Date(this.dateStart) : null
            if (newDate.length != 0) {
                this.showReport()
            }
        }
    },
    mixins: [dataUI, watchUI, componentsUI, methodsUI, globalMethodsReport],
});

if (document.getElementById("admin-index") !== null) {
    adminApp.mount("#admin-index");
}
