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
import { faInstagram } from '@fortawesome/free-brands-svg-icons';
import { faFacebook } from '@fortawesome/free-brands-svg-icons';
import { faTwitter } from '@fortawesome/free-brands-svg-icons';
import { faYoutube } from '@fortawesome/free-brands-svg-icons';
import { faBars } from '@fortawesome/free-solid-svg-icons';
import { faXmark } from '@fortawesome/free-solid-svg-icons';
import { faCaretDown } from '@fortawesome/free-solid-svg-icons';
import { faGear } from '@fortawesome/free-solid-svg-icons';


/* Agregamos los iconos a la libreria*/
library.add(faUser,faLock, faEye,faEyeSlash,faCircleExclamation,faSpinner,faInstagram,
faFacebook,faTwitter,faYoutube,faBars,faXmark,faCaretDown,faGear)

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
