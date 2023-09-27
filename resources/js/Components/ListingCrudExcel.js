export const ListingMixinMethods = {
    methods: {
        /**
         * Metodo que se encarga de agrupar las areas y niveles, y devolver un total para producir en excel
         */
        reportExcel() {
            let dataExcel = []; // Don de almacenara el array resultante
            let listExcel = this.getTotalArea();

            //Una vez acumulada la informacion por area y nivel
            this.controlTable.data.forEach((user, cursor) => {
                dataExcel.push(user);
                if (
                    (this.controlTable.data[cursor + 1] &&
                        this.controlTable.data[cursor].area !=
                            this.controlTable.data[cursor + 1].area) ||
                    !this.controlTable.data[cursor + 1]
                ) {
                    listExcel[0].forEach((field) => {
                        if (user.area == field.area) {
                            const percenTotalProy =
                                (field.tot_hor_proy / field.hor_ref) * 100;
                            const percenTotalAdmon =
                                (field.tot_hor_admon / field.hor_ref) * 100;
                            //Cargamos el total en funcion del tipo de directivo
                            dataExcel.push({
                                nombre:
                                    "<b>" +
                                    "Total de carga para".toUpperCase() +
                                    "</b>",
                                nivel:
                                    "<b>" + field.nivel.toUpperCase() + "</b>",
                                "%_carga_min_proy": Number(
                                    field["%_carga_min_proy"]
                                ).toLocaleString("de-DE"),
                                "%_carga_min_admon": Number(
                                    field["%_carga_min_admon"]
                                ).toLocaleString("de-DE"),
                                hor_esp_proy: Number(
                                    field.hor_esp_proy.toFixed(2)
                                ).toLocaleString("de-DE"),
                                hor_esp_admon: Number(
                                    field.hor_esp_admon.toFixed(2)
                                ).toLocaleString("de-DE"),
                                hor_ref: Number(
                                    field.hor_ref.toFixed(2)
                                ).toLocaleString("de-DE"),
                                tot_hor_proy: Number(
                                    field.tot_hor_proy.toFixed(2)
                                ).toLocaleString("de-DE"),
                                "%_hor_proy": Number(
                                    percenTotalProy.toFixed(2)
                                ).toLocaleString("de-DE"),
                                tot_hor_admon: Number(
                                    field.tot_hor_admon.toFixed(2)
                                ).toLocaleString("de-DE"),
                                "%_hor_admon": Number(
                                    percenTotalAdmon.toFixed(2)
                                ).toLocaleString("de-DE"),
                                tot_hor: Number(
                                    field.tot_hor.toFixed(2)
                                ).toLocaleString("de-DE"),
                                "%_tot_hor": Number(
                                    (
                                        percenTotalAdmon + percenTotalProy
                                    ).toFixed(2)
                                ).toLocaleString("de-DE"),
                            });
                        }
                    });
                    listExcel[1].forEach((areaTotal) => {
                        if (areaTotal.area == user.area)
                            dataExcel.push({
                                nombre:
                                    "<b>" +
                                    areaTotal.nombre.toUpperCase() +
                                    "</b>",
                                area:
                                    "<b>" +
                                    areaTotal.area.toUpperCase() +
                                    "</b>",
                                hor_esp_proy: Number(
                                    areaTotal["hor_esp_proy"].toFixed(2)
                                ).toLocaleString("de-DE"),
                                hor_esp_admon: Number(
                                    areaTotal["hor_esp_admon"].toFixed(2)
                                ).toLocaleString("de-DE"),
                                hor_ref: Number(
                                    areaTotal["hor_ref"].toFixed(2)
                                ).toLocaleString("de-DE"),
                                tot_hor_proy: Number(
                                    areaTotal["tot_hor_proy"].toFixed(2)
                                ).toLocaleString("de-DE"),
                                tot_hor_admon: Number(
                                    areaTotal["tot_hor_admon"].toFixed(2)
                                ).toLocaleString("de-DE"),
                                tot_hor: Number(
                                    areaTotal["tot_hor"].toFixed(2)
                                ).toLocaleString("de-DE"),
                            });
                    });
                    dataExcel.push({});
                }
            });

            return dataExcel;
        },
        /**
         * Obtiene un reporte resumido de las areas
         */
        reportResume() {
            let dataExcel = [];
            let listExcel = this.getTotalArea();

            //Cargamos primero el total de los niveles y luego el total general
            listExcel[1].forEach((fieldTotal) => {
                listExcel[0].forEach((field) => {
                    const percenTotalProy =
                        (field.tot_hor_proy / field.hor_ref) * 100;
                    const percenTotalAdmon =
                        (field.tot_hor_admon / field.hor_ref) * 100;
                    if (fieldTotal.area == field.area)
                        dataExcel.push({
                            titulo:
                                "<b>" +
                                "Total de carga para".toUpperCase() +
                                "</b>",
                            area: "",
                            nivel: "<b>" + field.nivel.toUpperCase() + "</b>",
                            "%_carga_min_proy": Number(
                                field["%_carga_min_proy"]
                            ).toLocaleString("de-DE"),
                            "%_carga_min_admon": Number(
                                field["%_carga_min_admon"]
                            ).toLocaleString("de-DE"),
                            hor_esp_proy: Number(
                                field.hor_esp_proy.toFixed(2)
                            ).toLocaleString("de-DE"),
                            hor_esp_admon: Number(
                                field.hor_esp_admon.toFixed(2)
                            ).toLocaleString("de-DE"),
                            hor_ref: Number(
                                field.hor_ref.toFixed(2)
                            ).toLocaleString("de-DE"),
                            tot_hor_proy: Number(
                                field.tot_hor_proy.toFixed(2)
                            ).toLocaleString("de-DE"),
                            "%_hor_proy": Number(
                                percenTotalProy.toFixed(2)
                            ).toLocaleString("de-DE"),
                            tot_hor_admon: Number(
                                field.tot_hor_admon.toFixed(2)
                            ).toLocaleString("de-DE"),
                            "%_hor_admon": Number(
                                percenTotalAdmon.toFixed(2)
                            ).toLocaleString("de-DE"),
                            tot_hor: Number(
                                field.tot_hor.toFixed(2)
                            ).toLocaleString("de-DE"),
                            "%_tot_hor": Number(
                                (percenTotalAdmon + percenTotalProy).toFixed(2)
                            ).toLocaleString("de-DE"),
                            dif_hor_proy: Number(
                                (
                                    field.tot_hor_proy - field.hor_esp_proy
                                ).toFixed(2)
                            ).toLocaleString("de-DE"),
                            "%_dif_hor_proy": Number(
                                (
                                    percenTotalProy - field["%_carga_min_proy"]
                                ).toFixed(2)
                            ).toLocaleString("de-DE"),
                            dif_hor_admon: Number(
                                (
                                    field.tot_hor_admon - field.hor_esp_admon
                                ).toFixed(2)
                            ).toLocaleString("de-DE"),
                            "%_dif_hor_admon": Number(
                                (
                                    percenTotalAdmon -
                                    field["%_carga_min_admon"]
                                ).toFixed(2)
                            ).toLocaleString("de-DE"),
                        });
                });

                const percenProy =
                    (fieldTotal.hor_esp_proy / fieldTotal.hor_ref) * 100;
                const percenAdmon =
                    (fieldTotal.hor_esp_admon / fieldTotal.hor_ref) * 100;
                const percenRealProy =
                    (fieldTotal.tot_hor_proy / fieldTotal.hor_ref) * 100;
                const percenRealAdmon =
                    (fieldTotal.tot_hor_admon / fieldTotal.hor_ref) * 100;
                //Cargamos el area total
                dataExcel.push({
                    titulo: "<b>" + fieldTotal.nombre.toUpperCase() + "</b>",
                    area: "<b>" + fieldTotal.area.toUpperCase() + "</b>",
                    "%_carga_min_proy":
                        Number(percenProy).toLocaleString("de-DE"),
                    "%_carga_min_admon":
                        Number(percenAdmon).toLocaleString("de-DE"),
                    hor_esp_proy: Number(
                        fieldTotal["hor_esp_proy"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    hor_esp_admon: Number(
                        fieldTotal["hor_esp_admon"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    hor_ref: Number(
                        fieldTotal["hor_ref"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    tot_hor_proy: Number(
                        fieldTotal["tot_hor_proy"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_hor_proy": Number(
                        percenRealProy.toFixed(2)
                    ).toLocaleString("de-DE"),
                    tot_hor_admon: Number(
                        fieldTotal["tot_hor_admon"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_hor_admon": Number(
                        percenRealAdmon.toFixed(2)
                    ).toLocaleString("de-DE"),
                    tot_hor: Number(
                        fieldTotal["tot_hor"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_tot_hor": Number(
                        (percenRealAdmon + percenRealProy).toFixed(2)
                    ).toLocaleString("de-DE"),
                    dif_hor_proy: Number(
                        (
                            fieldTotal.tot_hor_proy - fieldTotal.hor_esp_proy
                        ).toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_dif_hor_proy": Number(
                        (percenRealProy - percenProy).toFixed(2)
                    ).toLocaleString("de-DE"),
                    dif_hor_admon: Number(
                        (
                            fieldTotal.tot_hor_admon - fieldTotal.hor_esp_admon
                        ).toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_dif_hor_admon": Number(
                        (percenRealAdmon - percenAdmon).toFixed(2)
                    ).toLocaleString("de-DE"),
                });
                dataExcel.push({});
            });

            return dataExcel;
        },
        /** Genera un excel del reporte consolidado de las areas */
        reportConsolidated() {
            let dataExcel = [];
            let listExcel = this.getTotalArea();
            let listTotal = this.getTotalAcum();

            //Cargamos primero el total de los niveles y luego el total general
            listExcel[1].forEach((fieldTotal) => {
                const percenProy =
                    (fieldTotal.hor_esp_proy / fieldTotal.hor_ref) * 100;
                const percenAdmon =
                    (fieldTotal.hor_esp_admon / fieldTotal.hor_ref) * 100;
                const percenRealProy =
                    (fieldTotal.tot_hor_proy / fieldTotal.hor_ref) * 100;
                const percenRealAdmon =
                    (fieldTotal.tot_hor_admon / fieldTotal.hor_ref) * 100;
                //Cargamos el area total
                dataExcel.push({
                    titulo: "<b>" + fieldTotal.nombre.toUpperCase() + "</b>",
                    area: "<b>" + fieldTotal.area.toUpperCase() + "</b>",
                    "%_carga_min_proy":
                        Number(percenProy).toLocaleString("de-DE"),
                    "%_carga_min_admon":
                        Number(percenAdmon).toLocaleString("de-DE"),
                    hor_esp_proy: Number(
                        fieldTotal["hor_esp_proy"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    hor_esp_admon: Number(
                        fieldTotal["hor_esp_admon"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    hor_ref: Number(
                        fieldTotal["hor_ref"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    tot_hor_proy: Number(
                        fieldTotal["tot_hor_proy"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_hor_proy": Number(
                        percenRealProy.toFixed(2)
                    ).toLocaleString("de-DE"),
                    tot_hor_admon: Number(
                        fieldTotal["tot_hor_admon"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_hor_admon": Number(
                        percenRealAdmon.toFixed(2)
                    ).toLocaleString("de-DE"),
                    tot_hor: Number(
                        fieldTotal["tot_hor"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_tot_hor": Number(
                        (percenRealAdmon + percenRealProy).toFixed(2)
                    ).toLocaleString("de-DE"),
                    dif_hor_proy: Number(
                        (
                            fieldTotal.tot_hor_proy - fieldTotal.hor_esp_proy
                        ).toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_dif_hor_proy": Number(
                        (percenRealProy - percenProy).toFixed(2)
                    ).toLocaleString("de-DE"),
                    dif_hor_admon: Number(
                        (
                            fieldTotal.tot_hor_admon - fieldTotal.hor_esp_admon
                        ).toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_dif_hor_admon": Number(
                        (percenRealAdmon - percenAdmon).toFixed(2)
                    ).toLocaleString("de-DE"),
                },{//Recuadro total de la division
                    "%_carga_min_proy":"<b>Proy</b>",
                    "%_carga_min_admon": "<b>Admon</b>",
                    hor_esp_proy: "<b>Total</b>",
                    hor_esp_admon: "<b>%_proy</b>",
                },
                {
                    area: "<b>Reales</b>",
                    "%_carga_min_proy":Number(
                        fieldTotal["tot_hor_proy"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_carga_min_admon": Number(
                        fieldTotal["tot_hor_admon"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    hor_esp_proy: Number(
                        fieldTotal["tot_hor"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    hor_esp_admon: Number(
                        percenRealProy.toFixed(2)
                    ).toLocaleString("de-DE"),
                },
                {
                    area: "<b>Estandar</b>",
                    "%_carga_min_proy":Number(
                        fieldTotal["hor_esp_proy"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_carga_min_admon": Number(
                        fieldTotal["hor_esp_admon"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    hor_esp_proy: Number(
                        fieldTotal["hor_ref"].toFixed(2)
                    ).toLocaleString("de-DE"),
                    hor_esp_admon: Number(percenProy).toLocaleString("de-DE"),
                },
                {
                    area: "<b>Diferencia</b>",
                    "%_carga_min_proy":Number(
                        (
                            fieldTotal.tot_hor_proy - fieldTotal.hor_esp_proy
                        ).toFixed(2)
                    ).toLocaleString("de-DE"),
                    "%_carga_min_admon": Number(
                        (
                            fieldTotal.tot_hor_admon - fieldTotal.hor_esp_admon
                        ).toFixed(2)
                    ).toLocaleString("de-DE"),
                    hor_esp_proy: Number(
                        (
                            fieldTotal.tot_hor - fieldTotal.hor_ref
                        ).toFixed(2)
                    ).toLocaleString("de-DE"),
                    hor_esp_admon: Number(
                        (percenRealProy - percenProy).toFixed(2)
                    ).toLocaleString("de-DE"),
                },{});
            });
            //Imprimimos el total
            dataExcel.push({
                titulo: "<b>Total General</b>",
                "%_carga_min_proy": "<b>Proy</b>",
                "%_carga_min_admon": "<b>Admon</b>",
                hor_esp_proy: "<b>Total</b>",
                hor_esp_admon: "<b>%_proy</b>",
                hor_ref: "<b>%_admon</b>",
            },
            {
                area: "<b>Reales</b>",
                "%_carga_min_proy": Number((listTotal.realProy).toFixed(2)).toLocaleString("de-DE"),
                "%_carga_min_admon": Number((listTotal.realAdmon).toFixed(2)).toLocaleString("de-DE"),
                hor_esp_proy: Number((listTotal.realAdmon + listTotal.realProy).toFixed(2)).toLocaleString("de-DE"),
                hor_esp_admon: Number((listTotal.perRealProy).toFixed(2)).toLocaleString("de-DE"),
                hor_ref: Number((listTotal.perRealAdmon).toFixed(2)).toLocaleString("de-DE")
            },
            {
                area: "<b>Estimadas</b>",
                "%_carga_min_proy": Number((listTotal.refProy).toFixed(2)).toLocaleString("de-DE"),
                "%_carga_min_admon": Number((listTotal.refAdmon).toFixed(2)).toLocaleString("de-DE"),
                hor_esp_proy: Number((listTotal.refAdmon + listTotal.refProy).toFixed(2)).toLocaleString("de-DE"),
                hor_esp_admon: Number((listTotal.perRefProy).toFixed(2)).toLocaleString("de-DE"),
                hor_ref: Number((listTotal.perRefAdmon).toFixed(2)).toLocaleString("de-DE")
            },
            {
                area: "<b>Diferencia</b>",
                "%_carga_min_proy": Number((listTotal.realAdmon - listTotal.refAdmon).toFixed(2)).toLocaleString("de-DE"),
                "%_carga_min_admon": Number((listTotal.realProy - listTotal.refProy).toFixed(2)).toLocaleString("de-DE"),
                hor_esp_proy: Number(((listTotal.realAdmon + listTotal.realProy) - (listTotal.refAdmon + listTotal.refProy)).toFixed(2)).toLocaleString("de-DE"),
                hor_esp_admon: Number((listTotal.perRealProy - listTotal.perRefProy).toFixed(2)).toLocaleString("de-DE"),
                hor_ref: Number((listTotal.perRealAdmon - listTotal.perRefAdmon).toFixed(2)).toLocaleString("de-DE")
            })

            return dataExcel;
        },
        getTotalArea() {
            let listDirectiveExcel = this.controlTable.data.reduce(
                (acum, field) => {
                    //Creamos un key
                    const key = field.area + "-" + field.nivel;
                    if (!acum[key]) {
                        acum[key] = {
                            area: field.area,
                            nivel: field.nivel,
                            "%_carga_min_proy": this.convertNumber(
                                field["%_carga_min_proy"]
                            ),
                            "%_carga_min_admon": this.convertNumber(
                                field["%_carga_min_admon"]
                            ),
                            hor_esp_proy: this.convertNumber(
                                field["hor_esp_proy"]
                            ),
                            hor_esp_admon: this.convertNumber(
                                field["hor_esp_admon"]
                            ),
                            hor_ref: this.convertNumber(field["hor_ref"]),
                            tot_hor_proy: this.convertNumber(
                                field["tot_hor_proy"]
                            ),
                            tot_hor_admon: this.convertNumber(
                                field["tot_hor_admon"]
                            ),
                            tot_hor: this.convertNumber(field["tot_hor"]),
                        };
                    } else {
                        acum[key].hor_esp_proy += this.convertNumber(
                            field["hor_esp_proy"]
                        );
                        acum[key].hor_esp_admon += this.convertNumber(
                            field["hor_esp_admon"]
                        );
                        acum[key].hor_ref += this.convertNumber(
                            field["hor_ref"]
                        );
                        acum[key].tot_hor_proy += this.convertNumber(
                            field["tot_hor_proy"]
                        );
                        acum[key].tot_hor_admon += this.convertNumber(
                            field["tot_hor_admon"]
                        );
                        acum[key].tot_hor += this.convertNumber(
                            field["tot_hor"]
                        );
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
        getTotalAcum(){
            let listExcel = this.getTotalArea();
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
            listExcel[1].forEach((field) => {
                hourRef.refProy += parseFloat(field.hor_esp_proy);
                hourRef.refAdmon += parseFloat(field.hor_esp_admon);
                hourRef.realAdmon += parseFloat(field.tot_hor_admon);
                hourRef.realProy += parseFloat(field.tot_hor_proy);
                hourRef.perRefProy += parseFloat((field.hor_esp_proy / field.hor_ref) * 100);
                hourRef.perRefAdmon += parseFloat((field.hor_esp_admon / field.hor_ref) * 100);
                hourRef.perRealProy += parseFloat((field.tot_hor_proy / field.hor_ref) * 100);
                hourRef.perRealAdmon += parseFloat((field.tot_hor_admon / field.hor_ref) * 100);
            })

            return hourRef
        }
    },
};
