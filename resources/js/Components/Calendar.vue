<template>
    <DatePicker v-model="date" is-required>
        <template #default="{ togglePopover }">
            <font-awesome string-icon="fa-solid fa-calendar" @click="togglePopover" class="aLink"></font-awesome>
        </template>
    </DatePicker>
</template>

<script>
import { setupCalendar, Calendar, DatePicker } from 'v-calendar';
import 'v-calendar/style.css';
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";

export default {
    data() {
        return {
            date: '',
            dateDTO: { day: 0, month: 0, year: 0 } // Objeto de transferencia
        }
    },
    created() {
        this.date = ''
    },
    watch: {
        date(newDate) {
            this.dateDTO = {
                day: this.date.getDate(),
                month: this.date.getMonth() + 1,
                year: this.date.getFullYear()
            }

            //Preparamos el formato
            if (this.dateDTO.month < 10) this.dateDTO.month = `0${this.dateDTO.month}`;
            if (this.dateDTO.day < 10) this.dateDTO.day = `0${this.dateDTO.day}`;

            this.$emit('toInput', this.dateDTO);
        }
    },
    components: { DatePicker, FontAwesome }
}
</script>
