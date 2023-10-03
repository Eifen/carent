import { globalMethodsReport as formatReport } from "./UiComponents/Reports/GlobalReportMethods";
export const ListingMixinMethods = {
    methods: {
        /**
         * Metodo que estructura un reporte total del personal y el total de area tanto por nivel como total global
         * @returns Retorna un array con la informacion del reporte
         */
        reportExcel() {
            const prepareList = this.getTotalArea();
            let dataExcel = []; //Array que almacena lo que se va a mostrar
            //Recorremos la informacion de las areas
            prepareList[1].forEach((field) => {
                //Luego la data total para insertar los primeros parametros
                this.controlTable.data.forEach((field2) => {
                    if (field2.area == field.area) dataExcel.push(field2);
                })
                //Tras insertar toda el area, insertamos el total por niveles
                prepareList[0].forEach((field3) => {
                    //Preparamos los calculos de porcentaje
                    const refPercenProy = ((field3.hor_esp_proy / field3.hor_ref) * 100);
                    const refPercenAdmon = ((field3.hor_esp_admon / field3.hor_ref) * 100);
                    const proyPercen = ((field3.tot_hor_proy / field3.hor_ref) * 100);
                    const admonPercen = ((field3.tot_hor_admon / field3.hor_ref) * 100);
                    if (field3.area == field.area) {
                        dataExcel.push(formatReport.methods.formatDirectiveObject([
                            field3.nombre.toUpperCase().bold(),
                            "",
                            field3.nivel.toUpperCase().bold(),
                            formatReport.methods.formatReportNumber(refPercenProy),
                            formatReport.methods.formatReportNumber(refPercenAdmon),
                            formatReport.methods.formatReportNumber(field3.hor_esp_proy),
                            formatReport.methods.formatReportNumber(field3.hor_esp_admon),
                            formatReport.methods.formatReportNumber(field3.hor_ref),
                            refPercenProy > proyPercen ? "DE" : "E",
                            formatReport.methods.formatReportNumber(field3.tot_hor_proy),
                            formatReport.methods.formatReportNumber(proyPercen),
                            formatReport.methods.formatReportNumber(field3.tot_hor_admon),
                            formatReport.methods.formatReportNumber(admonPercen),
                            formatReport.methods.formatReportNumber(field3.tot_hor),
                            formatReport.methods.formatReportNumber(proyPercen + admonPercen)
                        ]))
                    }
                })
                //Finalmente cargamos el total
                const refPercenProy = ((field.hor_esp_proy / field.hor_ref) * 100);
                const refPercenAdmon = ((field.hor_esp_admon / field.hor_ref) * 100);
                const proyPercen = ((field.tot_hor_proy / field.hor_ref) * 100);
                const admonPercen = ((field.tot_hor_admon / field.hor_ref) * 100);
                dataExcel.push(formatReport.methods.formatDirectiveObject([
                    field.nombre.toUpperCase().bold(),
                    field.area.toUpperCase().bold(),
                    "",
                    formatReport.methods.formatReportNumber(refPercenProy),
                    formatReport.methods.formatReportNumber(refPercenAdmon),
                    formatReport.methods.formatReportNumber(field.hor_esp_proy),
                    formatReport.methods.formatReportNumber(field.hor_esp_admon),
                    formatReport.methods.formatReportNumber(field.hor_ref),
                    refPercenProy > proyPercen ? "DE" : "E",
                    formatReport.methods.formatReportNumber(field.tot_hor_proy),
                    formatReport.methods.formatReportNumber(proyPercen),
                    formatReport.methods.formatReportNumber(field.tot_hor_admon),
                    formatReport.methods.formatReportNumber(admonPercen),
                    formatReport.methods.formatReportNumber(field.tot_hor),
                    formatReport.methods.formatReportNumber(proyPercen + admonPercen)
                ]), {});
            })
            return dataExcel
        },
        /**
         * Reporte que muestra solo el total de areas tanto por nivel como global
         * @returns Retorna un array con la informacion de las columnas para excel
         */
        reportResume() {
            const prepareList = this.getTotalArea();
            let dataExcel = []; //Array que almacena lo que se va a mostrar
            //Recorremos la informacion de las areas
            prepareList[1].forEach((field) => {
                //Insertamos el total por niveles
                prepareList[0].forEach((field3) => {
                    //Preparamos los calculos de porcentaje
                    const refPercenProy = ((field3.hor_esp_proy / field3.hor_ref) * 100);
                    const refPercenAdmon = ((field3.hor_esp_admon / field3.hor_ref) * 100);
                    const proyPercen = ((field3.tot_hor_proy / field3.hor_ref) * 100);
                    const admonPercen = ((field3.tot_hor_admon / field3.hor_ref) * 100);
                    if (field3.area == field.area) {
                        dataExcel.push(formatReport.methods.formatDirectiveObject([
                            field3.nombre.toUpperCase().bold(),
                            "",
                            field3.nivel.toUpperCase().bold(),
                            formatReport.methods.formatReportNumber(refPercenProy),
                            formatReport.methods.formatReportNumber(refPercenAdmon),
                            formatReport.methods.formatReportNumber(field3.hor_esp_proy),
                            formatReport.methods.formatReportNumber(field3.hor_esp_admon),
                            formatReport.methods.formatReportNumber(field3.hor_ref),
                            refPercenProy > proyPercen ? "DE" : "E",
                            formatReport.methods.formatReportNumber(field3.tot_hor_proy),
                            formatReport.methods.formatReportNumber(proyPercen),
                            formatReport.methods.formatReportNumber(field3.tot_hor_admon),
                            formatReport.methods.formatReportNumber(admonPercen),
                            formatReport.methods.formatReportNumber(field3.tot_hor),
                            formatReport.methods.formatReportNumber(proyPercen + admonPercen)
                        ]))
                    }
                })
                //Finalmente cargamos el total
                const refPercenProy = ((field.hor_esp_proy / field.hor_ref) * 100);
                const refPercenAdmon = ((field.hor_esp_admon / field.hor_ref) * 100);
                const proyPercen = ((field.tot_hor_proy / field.hor_ref) * 100);
                const admonPercen = ((field.tot_hor_admon / field.hor_ref) * 100);
                dataExcel.push(formatReport.methods.formatDirectiveObject([
                    field.nombre.toUpperCase().bold(),
                    field.area.toUpperCase().bold(),
                    "",
                    formatReport.methods.formatReportNumber(refPercenProy),
                    formatReport.methods.formatReportNumber(refPercenAdmon),
                    formatReport.methods.formatReportNumber(field.hor_esp_proy),
                    formatReport.methods.formatReportNumber(field.hor_esp_admon),
                    formatReport.methods.formatReportNumber(field.hor_ref),
                    refPercenProy > proyPercen ? "DE" : "E",
                    formatReport.methods.formatReportNumber(field.tot_hor_proy),
                    formatReport.methods.formatReportNumber(proyPercen),
                    formatReport.methods.formatReportNumber(field.tot_hor_admon),
                    formatReport.methods.formatReportNumber(admonPercen),
                    formatReport.methods.formatReportNumber(field.tot_hor),
                    formatReport.methods.formatReportNumber(proyPercen + admonPercen)
                ]), {});
            })
            return dataExcel
        },
        reportConsolidated() {
            let dataExcel = [];
            const prepareList = this.getTotalArea();
            const prepareTotal = this.getTotalAcum();

            //Cargamos primero el total de los niveles y luego el total general
            prepareList[1].forEach((fieldTotal) => {
                const refPercenProy = ((fieldTotal.hor_esp_proy / fieldTotal.hor_ref) * 100);
                const refPercenAdmon = ((fieldTotal.hor_esp_admon / fieldTotal.hor_ref) * 100);
                const proyPercen = ((fieldTotal.tot_hor_proy / fieldTotal.hor_ref) * 100);
                const admonPercen = ((fieldTotal.tot_hor_admon / fieldTotal.hor_ref) * 100);
                const difHorProy = fieldTotal.tot_hor_proy - fieldTotal.hor_esp_proy;
                const diffPercenProy = proyPercen - refPercenProy;
                const difHorAdmon = fieldTotal.tot_hor_admon - fieldTotal.hor_esp_admon;
                const diffPercenAdmon = admonPercen - refPercenAdmon;
                const diffTotal = fieldTotal.tot_hor - fieldTotal.hor_ref
                dataExcel.push(formatReport.methods.formatConsolidatedObject([
                    fieldTotal.nombre.toUpperCase().bold(),
                    fieldTotal.area.toUpperCase().bold(),
                    formatReport.methods.formatReportNumber(refPercenProy),
                    formatReport.methods.formatReportNumber(refPercenAdmon),
                    formatReport.methods.formatReportNumber(fieldTotal.hor_esp_proy),
                    formatReport.methods.formatReportNumber(fieldTotal.hor_esp_admon),
                    formatReport.methods.formatReportNumber(fieldTotal.hor_ref),
                    formatReport.methods.formatReportNumber(fieldTotal.tot_hor_proy),
                    formatReport.methods.formatReportNumber(proyPercen),
                    formatReport.methods.formatReportNumber(fieldTotal.tot_hor_admon),
                    formatReport.methods.formatReportNumber(admonPercen),
                    formatReport.methods.formatReportNumber(fieldTotal.tot_hor),
                    formatReport.methods.formatReportNumber(proyPercen + admonPercen),
                    formatReport.methods.formatReportNumber(difHorProy),
                    formatReport.methods.formatReportNumber(diffPercenProy),
                    formatReport.methods.formatReportNumber(difHorAdmon),
                    formatReport.methods.formatReportNumber(diffPercenAdmon),
                ]), formatReport.methods.formatConsolidatedObject([
                    "",
                    "",
                    "<b>Proy</b>",
                    "<b>Admon</b>",
                    "<b>Total</b>",
                    "<b>%_proy</b>",
                ]), formatReport.methods.formatConsolidatedObject([
                    "",
                    "<b>Reales</b>",
                    formatReport.methods.formatReportNumber(fieldTotal.tot_hor_proy),
                    formatReport.methods.formatReportNumber(fieldTotal.tot_hor_admon),
                    formatReport.methods.formatReportNumber(fieldTotal.tot_hor),
                    formatReport.methods.formatReportNumber(proyPercen),
                ]), formatReport.methods.formatConsolidatedObject([
                    "",
                    "<b>Estimadas</b>",
                    formatReport.methods.formatReportNumber(fieldTotal.hor_esp_proy),
                    formatReport.methods.formatReportNumber(fieldTotal.hor_esp_admon),
                    formatReport.methods.formatReportNumber(fieldTotal.hor_ref),
                    formatReport.methods.formatReportNumber(refPercenProy),
                ]), formatReport.methods.formatConsolidatedObject([
                    "",
                    "<b>Diferencia</b>",
                    formatReport.methods.formatReportNumber(difHorProy),
                    formatReport.methods.formatReportNumber(difHorAdmon),
                    formatReport.methods.formatReportNumber(diffTotal),
                    formatReport.methods.formatReportNumber(diffPercenProy),
                ]), {})
            });
            //Imprimimos el total
            dataExcel.push(formatReport.methods.formatConsolidatedObject([
                "<b>TOTAL</b>",
                "<b>GENERAL</b>",
                "<b>Proy</b>",
                "<b>Admon</b>",
                "<b>Total</b>",
                "<b>%_proy</b>",
                "<b>%_admon</b>",
            ]), formatReport.methods.formatConsolidatedObject([
                "",
                "<b>Reales</b>",
                formatReport.methods.formatReportNumber(prepareTotal.realProy),
                formatReport.methods.formatReportNumber(prepareTotal.realAdmon),
                formatReport.methods.formatReportNumber((prepareTotal.realAdmon + prepareTotal.realProy)),
                formatReport.methods.formatReportNumber(prepareTotal.perRealProy),
                formatReport.methods.formatReportNumber(prepareTotal.perRealAdmon),
            ]), formatReport.methods.formatConsolidatedObject([
                "",
                "<b>Estimadas</b>",
                formatReport.methods.formatReportNumber(prepareTotal.refProy),
                formatReport.methods.formatReportNumber(prepareTotal.refAdmon),
                formatReport.methods.formatReportNumber((prepareTotal.refAdmon + prepareTotal.refProy)),
                formatReport.methods.formatReportNumber(prepareTotal.perRefProy),
                formatReport.methods.formatReportNumber(prepareTotal.perRefAdmon),
            ]), formatReport.methods.formatConsolidatedObject([
                "",
                "<b>Diferencia</b>",
                formatReport.methods.formatReportNumber((prepareTotal.realProy - prepareTotal.refProy)),
                formatReport.methods.formatReportNumber((prepareTotal.realAdmon - prepareTotal.refAdmon)),
                formatReport.methods.formatReportNumber(((prepareTotal.realAdmon + prepareTotal.realProy) - (prepareTotal.refAdmon + prepareTotal.refProy))),
                formatReport.methods.formatReportNumber((prepareTotal.perRealProy - prepareTotal.perRefProy)),
                formatReport.methods.formatReportNumber((prepareTotal.perRealAdmon - prepareTotal.perRefAdmon)),
            ]))

            return dataExcel;
        },
        /**
         * Reporte que totaliza las areas tanto por nivel como global
         * @returns Devuelve una tupla donde [0] = total por nivel y [1] = total global por area
         */
        getTotalArea() {
            let listDirectiveExcel = this.controlTable.data.reduce(
                (acum, field) => {
                    //Creamos un key
                    const key = field.area + "-" + field.nivel;
                    if (!acum[key]) {
                        acum[key] = {
                            nombre: "Total para",
                            area: field.area,
                            nivel: field.nivel,
                            "%_carga_min_proy": this.convertNumber(field["%_carga_min_proy"]),
                            "%_carga_min_admon": this.convertNumber(field["%_carga_min_admon"]),
                            hor_esp_proy: this.convertNumber(field["hor_esp_proy"]),
                            hor_esp_admon: this.convertNumber(field["hor_esp_admon"]),
                            hor_ref: this.convertNumber(field["hor_ref"]),
                            tot_hor_proy: this.convertNumber(field["tot_hor_proy"]),
                            tot_hor_admon: this.convertNumber(field["tot_hor_admon"]),
                            tot_hor: this.convertNumber(field["tot_hor"]),
                        };
                    } else {
                        acum[key].hor_esp_proy += this.convertNumber(field["hor_esp_proy"]);
                        acum[key].hor_esp_admon += this.convertNumber(field["hor_esp_admon"]);
                        acum[key].hor_ref += this.convertNumber(field["hor_ref"]);
                        acum[key].tot_hor_proy += this.convertNumber(field["tot_hor_proy"]);
                        acum[key].tot_hor_admon += this.convertNumber(field["tot_hor_admon"]);
                        acum[key].tot_hor += this.convertNumber(field["tot_hor"]);
                    }
                    return acum;
                },
                {}
            );
            //Transformamos el objeto a un array
            listDirectiveExcel = Object.values(listDirectiveExcel);
            //Reducimos para el total general
            let listTotalDirectiveExcel = listDirectiveExcel.reduce(
                (acum, field) => {
                    //Creamos un key
                    const key = field.area;
                    if (!acum[key]) {
                        acum[key] = {
                            nombre: "Total de",
                            area: field.area,
                            hor_esp_proy: field["hor_esp_proy"],
                            hor_esp_admon: field["hor_esp_admon"],
                            hor_ref: field["hor_ref"],
                            tot_hor_proy: field["tot_hor_proy"],
                            tot_hor_admon: field["tot_hor_admon"],
                            tot_hor: field["tot_hor"],
                        };
                    } else {
                        acum[key].hor_esp_proy += field["hor_esp_proy"];
                        acum[key].hor_esp_admon += field["hor_esp_admon"];
                        acum[key].hor_ref += field["hor_ref"];
                        acum[key].tot_hor_proy += field["tot_hor_proy"];
                        acum[key].tot_hor_admon += field["tot_hor_admon"];
                        acum[key].tot_hor += field["tot_hor"];
                    }
                    return acum;
                },
                {}
            );

            //Transformamos a array
            listTotalDirectiveExcel = Object.values(listTotalDirectiveExcel);

            return [listDirectiveExcel, listTotalDirectiveExcel];
        },
        /**
         * Metodo que devuelve el total global de horas por area
         * @returns Retorna un objeto con el total tanto de horas referencia como reales
         */
        getTotalAcum() {
            const prepareList = this.getTotalArea();
            let hourRef = {
                refProy: 0,
                refAdmon: 0,
                realProy: 0,
                realAdmon: 0,
                perRefProy: 0,
                perRefAdmon: 0,
                perRealProy: 0,
                perRealAdmon: 0,
            }
            //Hacemos un foreach de la lista por area
            prepareList[1].forEach((field) => {
                hourRef.refProy += parseFloat(field.hor_esp_proy);
                hourRef.refAdmon += parseFloat(field.hor_esp_admon);
                hourRef.realAdmon += parseFloat(field.tot_hor_admon);
                hourRef.realProy += parseFloat(field.tot_hor_proy);
            })

            //Acomodamos el promedio de porcentajes
            hourRef.perRefProy += parseFloat((hourRef.refProy / (hourRef.refAdmon + hourRef.refProy)) * 100);
            hourRef.perRefAdmon += parseFloat((hourRef.refAdmon / (hourRef.refAdmon + hourRef.refProy)) * 100);
            hourRef.perRealProy += parseFloat((hourRef.realProy / (hourRef.refAdmon + hourRef.refProy)) * 100);
            hourRef.perRealAdmon += parseFloat((hourRef.realAdmon / (hourRef.refAdmon + hourRef.refProy)) * 100);
            return hourRef
        }
    },
};
