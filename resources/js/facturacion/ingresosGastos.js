import Vue from 'vue';

new Vue({

  el: '#app',
  data: {
    nombre: "Alexander",
    apellido: "Guilarte",
    direccion: {
      calle: "calle 1",
      apto: "apto 5"
    }
  },
  beforeCreate: function(){



  },
  created: function () {},
  mounted: function () {



  },
  methods:{

    cambiarNombre: function(){

      this.nombre = "David";
      this.apellido = "Molina";

    }

  }// Fin methods

});
