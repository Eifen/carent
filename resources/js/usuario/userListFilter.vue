<template>
    <div class="col-12">
        <form class="row">
            <div class="form-group col-12">
                <div class="input-group">
                    <span :class="formSearch.select.class">
                        <select @change="filterType"
                                class="form-select"
                                :disabled="formSearch.select.disabled"
                                v-model="formSearch.select.value">
                                <option value="" selected disabled>Consultar por</option>
                                <option value="1">Código</option>
                                <option value="2">Cédula</option>
                                <option value="3">Correo</option>
                                <option value="4">Nombre</option>
                        </select>
                    </span>
                    <input @keyup="evaluateField"
                           :class="formSearch.inputSearch.class"
                           :disabled="formSearch.inputSearch.disabled"
                           type="text"
                           v-model.trim="formSearch.inputSearch.value">
                    <span @click="searchUser" class="input-group-text" v-html="formSearch.submit.html"></span>
                    <div :class="formSearch.inputSearch.message.class"
                         v-if="formSearch.inputSearch.message.show"> {{ formSearch.inputSearch.message.text }} </div>
                </div>
            </div>
        </form>
    </div>
</template>


<script>

import { useUtils } from '../../js/components/Utils.js'
const utils = useUtils()
var self

export default {

    data() {
        return {
            formSearch: {
                submit: {
                    disabled: true,
                    html: "<i class='bi bi-search'></i>"
                },
                inputSearch: {
                    class: "form-control",
                    disabled: true,
                    message: {
                        class: "message",
                        show: false,
                        text: ""
                    },
                    value: ""
                },
                select: {
                    class: "input-group-text",
                    disabled: false,
                    value: ""
                }
            }
        }
    },
    beforeCreate: function() {
        self = this
    },
    methods: {

        clearErrorFilter: () =>  {
            self.formSearch.select.class = "input-group-text"
            self.formSearch.inputSearch.class = "form-control"
            self.formSearch.inputSearch.message.class = "message"
            self.formSearch.inputSearch.message.text = ""
            self.formSearch.inputSearch.message.show = false
        },
        evaluateField: () => {

            if(self.formSearch.inputSearch.value === "") {
                self.$emit('clearUsersList')
            }
            self.clearErrorFilter()

        },
        filterType: () => {
            let allowedValues = [1,2,3,4]
            let option = parseInt(self.formSearch.select.value)
            self.formSearch.inputSearch.disabled = (allowedValues.includes(option)) ? false : true
            self.clearErrorFilter()
        },
        searchUser: function(e) {

            if(this.formSearch.inputSearch.value !== "") {

                let data = {
                    data: self.formSearch.inputSearch.value,
                    searchBy: self.formSearch.select.value
                }
                self.$emit('searchBy', data)

            } else {

                this.formSearch.select.class = "input-group-text error"
                this.formSearch.inputSearch.class = "form-control error"
                this.formSearch.inputSearch.message.class = "message invalid-feedback"
                this.formSearch.inputSearch.message.text = "Campo requerido"
                this.formSearch.inputSearch.message.show = true
                //zenscroll.toY($(".inputSearch").offset().top - 100);

            }

        }

    }

}

</script>

<style lang="less" scoped src="../../less/usuario/userListFilter.less"></style>
