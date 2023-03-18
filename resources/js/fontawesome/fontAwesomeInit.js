//Importamos las librerias de fontawesome y vue
import { createApp } from 'vue/dist/vue.esm-bundler.js';
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faUser } from '@fortawesome/free-solid-svg-icons'
import { faLock } from '@fortawesome/free-solid-svg-icons'
import { faEye } from '@fortawesome/free-solid-svg-icons';
import { faEyeSlash } from '@fortawesome/free-solid-svg-icons';

//Importamos el componente
import ComponentFont from '../Components/FontAwesome/FontAwesome.vue';

/* Agregamos los iconos a la libreria*/
library.add(faUser,faLock, faEye,faEyeSlash)

/*Sincronizamos las librerias */
const App = createApp(ComponentFont).component('font-awesome-icon', FontAwesomeIcon).mount('#font-awesome-app')

