import { createApp } from 'vue/dist/vue.esm-bundler';
import FontAwesome from '../Components/FontAwesome/FontAwesome.vue';
import { Exceptions } from '../Excepciones/Excepciones';
import { UsersControl } from '../Models/UserModel';

const navApp = createApp ({
    data(){
        return {
            open: false, //Define el estado del toggle
            hamburgerMenu: "", //Define el estilo del menu mobile
            backgrounColor: "#fff",
            controlDropdown: { style:"" }
        }
    },
    methods:
    {
        goHome(){ window.location.href = "/" },
        statusBars(){
            this.open = !this.open

            this.open
            ? this.hamburgerMenu = "left:0%"
            : this.hamburgerMenu = "left: 100%"
        },
        openDropDown(){
            this.controlDropdown.style = `transform:scale(1);font-size: 1em;background: ${this.backgrounColor};`
        },
        closeDropDown() {
            this.controlDropdown.style = "transform:scale(0);font-size: 0em;background:none;"
        }
    },
    computed:{},
    watch:{},
    components: { FontAwesome }
});

if(document.getElementById("header-nav") !== null) navApp.mount("#header-nav");
