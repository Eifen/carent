<template>
        <FontAwesomeIcon :icon="stringIcon" :spin="isSpin"></FontAwesomeIcon>
</template>

<script>
//Importamos las librerias de fontawesome y vue
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faUser } from '@fortawesome/free-solid-svg-icons'
import { faLock } from '@fortawesome/free-solid-svg-icons'
import { faEye } from '@fortawesome/free-solid-svg-icons';
import { faEyeSlash } from '@fortawesome/free-solid-svg-icons';
import { faCircleExclamation } from '@fortawesome/free-solid-svg-icons';
import { faSpinner } from '@fortawesome/free-solid-svg-icons';

/* Agregamos los iconos a la libreria*/
library.add(faUser,faLock, faEye,faEyeSlash,faCircleExclamation,faSpinner)

export default {
    props: {
        stringIcon: String,
        isSpin: Boolean
    },
    components: { FontAwesomeIcon }
}
</script>

<style>

</style>
