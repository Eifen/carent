import { createApp } from 'vue/dist/vue.esm-bundler';
import { componentsUI, dataUI, CrudUi, methodsUI, watchUI } from './UIConfig';
import { Bar } from 'vue-chartjs';
import { BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const homeApp = createApp({
    data() {
        return {
            chartData: {
                labels: ['Horas estimadas', 'Horas cargadas', 'Horas proyectos', 'Horas admon'],
                datasets: [{
                    axis: 'y',
                    label: '',
                    data: [],
                    backgroundColor: []
                }]
            },
            chartOptions: {
                responsive: true,
                indexAxis: 'y'
            },
            months: [
                { name: 'Julio', index: "07" },
                { name: 'Agosto', index: "08" },
                { name: 'Septiembre', index: "09" },
                { name: 'Octubre', index: "10" },
                { name: 'Noviembre', index: "11" },
                { name: 'Diciembre', index: "12" },
                { name: 'Enero', index: "01" },
                { name: 'Febrero', index: "02" },
                { name: 'Marzo', index: "03" },
                { name: 'Abril', index: "04" },
                { name: 'Mayo', index: "05" },
                { name: 'Junio', index: "06" },
            ],
            isGraph: false,
            totalHours: 0, //Almacena el total de horas
            percenTotal: 0,
            percenProy: 0,
            percenAdmon: 0, //Almacena los porcentajes cargados
            actualMonth: 0, //Almacena el mes actual
            actualYear: '', //Almacena el ano actual
            initPeriod: 0, //Year inicial del periodo
            finishPeriod: 0, //Year final del periodo
            listMonth: [], //Lista de meses para el select
            monthSelect: 0 //Mes seleccionado
        }
    },
    mounted() {
        //
        const now = new Date();
        const month = now.getMonth() + 1
        this.actualMonth = (now.getMonth() > 5) ? (now.getMonth() - 6) : (6 + now.getMonth())
        this.monthSelect = month.toString().padStart(2, "0")
        //Preparamos el period dependiendo del mes actual
        if (month >= 1 && month <= 6) {
            this.initPeriod = now.getFullYear() - 1
            this.finishPeriod = now.getFullYear()
        }
        //En caso que empiece el nuevo ciclo
        if (month >= 7 && month < 12) {
            this.initPeriod = now.getFullYear()
            this.finishPeriod = now.getFullYear() + 1
        }
        //Preparamos el texto
        this.actualYear = `${this.initPeriod} - ${this.finishPeriod}`
        //Creamos la lista de meses hasta la fecha
        if (this.actualMonth === 0) this.listMonth.push(this.months[this.months.length - 1])
        for (let cursorMonth = 0; cursorMonth <= (this.actualMonth); cursorMonth++) {
            this.listMonth.push(this.months[cursorMonth])
        }
    },
    methods: {
        /**
         * Metodo que acomoda los estilos de las barras dependiendo de los porcentajes
         * @param {Number} percenToConvert Captura el porcentaje en valor numerico
         * @param {Boolean} type Valor booleano. true = color de barras, false = tipos de clase
         * @returns Retorna un String con el codigo hexadecimal del color de la barra
         */
        percenStyle(percenToConvert, type = false) {
            if (!type) {
                return percenToConvert >= 50 && percenToConvert < 100 ? '#fd7e14' : percenToConvert >= 0 && percenToConvert < 50 ? '#e3342f' : '#38c172'
            } else {
                return percenToConvert >= 50 && percenToConvert < 100 ? 'warning' : percenToConvert >= 0 && percenToConvert < 50 ? 'deficient' : 'eficient'
            }
        },
        /**
         * Transforma los porcentajes cambiando el decimal a una , en vez de .
         * @param {Number} percen Porcentaje a transformar
         */
        formatPercen(percen) {
            return Number(percen.toFixed(2)).toLocaleString('de-DE')
        }
    },
    computed: {
        updateChart() { return this.chartData },
        updateOptions() { return this.chartOptions }
    },
    watch: {
        listData() {
            this.totalHours = this.listData.real_proy + this.listData.real_admon
            this.percenTotal = (this.totalHours * 100) / this.listData.estimated_hour;
            this.percenProy = (this.listData.real_proy * 100) / (this.listData.estimated_proy == 0 ? this.listData.estimated_hour : this.listData.estimated_proy);
            this.percenAdmon = (this.listData.real_admon * 100) / this.listData.estimated_admon;
            const style = {
                total: this.percenStyle(this.percenTotal),
                proy: this.percenStyle(this.percenProy == 0 && this.listData.estimated_proy == 0 ? 100 : this.percenProy),
                admon: this.percenStyle(this.percenAdmon),
            }
            //Ubicamos el mes
            const monthReference = this.listData.month.split('-')[1]
            this.months.forEach(month => {
                if (month.index == monthReference) {
                    // Cargamos los charts
                    this.chartData.datasets[0] = {
                        label: month.name,
                        data: [this.listData.estimated_hour, this.totalHours, this.listData.real_proy, this.listData.real_admon],
                        backgroundColor: ['rgba(0,0,0,0.6)', style.total, style.proy, style.admon]
                    }
                }
            })
        },
        monthSelect(newselect) {
            let year = this.initPeriod
            if (parseInt(newselect) >= 1 && newselect <= 6 && this.actualMonth !== 0) year = this.finishPeriod
            //Llamamos a la informacion del usuario
            CrudUi.getTable("/log-user-info", this, { date: `${year}-${newselect}-01` });
        }
    },
    mixins: [componentsUI, dataUI, methodsUI, watchUI],
    components: { Bar },
});

if (document.getElementById("home-page") !== null) {
    homeApp.mount("#home-page")
}
