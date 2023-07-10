<template>
    <FontAwesomeIcon :icon="stringIcon" :spin="isSpin"></FontAwesomeIcon>
</template>

<script>
//Importamos las librerias de fontawesome y vue
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
//Solid icons
import {
    faUser,
    faLock,
    faEye,
    faEyeSlash,
    faCircleExclamation,
    faSpinner,
    faBars,
    faXmark,
    faCaretDown,
    faGear,
    faAngleLeft,
    faAngleRight,
    faAnglesLeft,
    faAnglesRight,
    faCalendar,
    faHashtag,
    faAt,
    faPlus,
    faCheck
} from "@fortawesome/free-solid-svg-icons";
//Brands Icons
import {
    faInstagram,
    faFacebook,
    faTwitter,
    faYoutube,
} from "@fortawesome/free-brands-svg-icons";

/* Agregamos los iconos a la libreria*/
library.add(
    faUser,
    faLock,
    faEye,
    faEyeSlash,
    faCircleExclamation,
    faSpinner,
    faInstagram,
    faFacebook,
    faTwitter,
    faYoutube,
    faBars,
    faXmark,
    faCaretDown,
    faGear,
    faAngleLeft,
    faAngleRight,
    faAnglesLeft,
    faAnglesRight,
    faCalendar,
    faHashtag,
    faAt,
    faPlus,
    faCheck
);

export default {
    props: {
        stringIcon: String,
        isSpin: Boolean,
    },
    components: { FontAwesomeIcon },
};
</script>

<style></style>
