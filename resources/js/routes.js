import VueRouter from 'vue-router'
const Home = { template: '<div>Home</div>' }
const About = { template: '<div>About</div>' }
import ListingUsers from './usuario/ListingUsers.vue'

const routes = new VueRouter({
    mode   : 'history',
    routes : [
        {
            path:'/',
            name: 'home',
            component: Home
        },
        {
            path:'/about',
            name: 'events',
            component: About
        },
        {
            path:'/usuarios',
            name: 'users',
            component: ListingUsers
        }
    ]
})

export default routes;
