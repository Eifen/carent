export const weeksFromDateMethod = {
    methods: {
        /**
         * Metodo que itera sobre un intervalo de fecha y devuelve su cantidad de semanadas
         * @param {*} startDate Fecha de inicio del intervalo
         * @param {*} endDate Fecha final del intervalo
         * @returns Array con la informacion por semana de sus rangos de fecha
         */
        getWeeksFromDateRange(startDate, endDate) {
            const weeks = [];

            // Obtener el primer día del mes de la fecha de inicio
            const firstDayOfMonth = new Date(
                startDate.getFullYear(),
                startDate.getMonth(),
                1
            );

            // Calcular la fecha del primer dia del mes
            let firstDayOfWeek = new Date(firstDayOfMonth);

            console.log(firstDayOfWeek);
            if (firstDayOfWeek.getDay() !== 1) {
                //Movemos al lunes si el mes inicia un sabado o domingo
                while (firstDayOfWeek.getDay() !== 1) {
                    firstDayOfWeek.setDate(firstDayOfWeek.getDate() - 1);
                }
            }

            // Iterar sobre las semanas
            let weekNumber = 1;
            while (firstDayOfWeek <= endDate) {
                // Calcular la fecha del final de semana
                let lastDayOfWeek = new Date(firstDayOfWeek);
                lastDayOfWeek.setDate(lastDayOfWeek.getDate() + 6);
                if (lastDayOfWeek > endDate) {
                    lastDayOfWeek = endDate;
                }

                // Agregar el intervalo de fechas a la semana correspondiente
                weeks.push({
                    number: `Semana ${weekNumber}`,
                    startDate: new Date(firstDayOfWeek),
                    endDate: new Date(lastDayOfWeek),
                });

                // Mover la fecha a la siguiente semana
                firstDayOfWeek.setDate(firstDayOfWeek.getDate() + 7);

                // Incrementar el número de semana
                weekNumber++;
            }

            return weeks;
        },
    },
};
