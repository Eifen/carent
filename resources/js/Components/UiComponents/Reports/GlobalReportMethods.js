export const globalMethodsReport = {
    methods: {
        /**
         * Metodo que pone el numero en formato de 000000,76
         * @param {Number} numberToFormat
         */
        formatReportNumber(numberToFormat) {
            const convertFixed = Number(numberToFormat).toFixed(2)
            return Number(convertFixed).toLocaleString("de-DE").replace(/\./g, "")
        },
        /**
         * Metodo que estructura el formato para los push de los reportes directivos
         * @param {*} objectParams Almacena los parametos de la lista del directivo, por defecto los coloca vacio
         * @returns Devuelve un objeto con el formato del objeto directivo
         */
        formatDirectiveObject([nombre = "",
            area = "",
            nivel = "",
            percen_proy_carg = "",
            percen_admon_carg = "",
            ref_proy = "",
            ref_admon = "",
            ref_total = "",
            eval_percen = "",
            proy_hour = "",
            percen_real_proy = "",
            admin_hour = "",
            percen_real_admon = "",
            total_hour = "",
            percen_total = ""]) {
            //Retorna el formato del objeto a mostrar
            return {
                nombre: nombre,
                area: area,
                nivel: nivel,
                "%_carga_min_proy": percen_proy_carg,
                "%_carga_min_admon": percen_admon_carg,
                hor_esp_proy: ref_proy,
                hor_esp_admon: ref_admon,
                hor_ref: ref_total,
                eval: eval_percen,
                tot_hor_proy: proy_hour,
                "%_hor_proy": percen_real_proy,
                tot_hor_admon: admin_hour,
                "%_hor_admon": percen_real_admon,
                tot_hor: total_hour,
                "%_tot_hor": percen_total,
            }
        },
        /**
         * Metodo que estructura el formato para los push del reporte consolidado
         * @param {*} objectParams Almacena los parametos de la lista del consolidado, por defecto los coloca vacio
         * @returns Devuelve un objeto con el formato del objeto consolidado
         */
        formatConsolidatedObject([titulo = "",
            area = "",
            percen_min_proy = "",
            percen_min_admon = "",
            hor_esp_proy = "",
            hor_esp_admon = "",
            hor_ref = "",
            tot_hor_proy = "",
            percen_hor_proy = "",
            tot_hor_admon = "",
            percen_hor_admon = "",
            tot_hor = "",
            percen_tot_hor = "",
            dif_hor_proy = "",
            percen_dif_hor_proy = "",
            dif_hor_admon = "",
            percen_dif_hor_admon = ""]) {
            //Retorna el formato del objeto a mostrar
            return {
                titulo: titulo,
                area: area,
                "%_carga_min_proy": percen_min_proy,
                "%_carga_min_admon": percen_min_admon,
                hor_esp_proy: hor_esp_proy,
                hor_esp_admon: hor_esp_admon,
                hor_ref: hor_ref,
                tot_hor_proy: tot_hor_proy,
                "%_hor_proy": percen_hor_proy,
                tot_hor_admon: tot_hor_admon,
                "%_hor_admon": percen_hor_admon,
                tot_hor: tot_hor,
                "%_tot_hor": percen_tot_hor,
                dif_hor_proy: dif_hor_proy,
                "%_dif_hor_proy": percen_dif_hor_proy,
                dif_hor_admon: dif_hor_admon,
                "%_dif_hor_admon": percen_dif_hor_admon
            }
        }
    },
}

