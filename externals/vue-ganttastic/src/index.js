import { createApp, ref } from 'vue/dist/vue.esm-bundler'
import ganttastic from'@infectoone/vue-ganttastic'

/*
export function pfmGant(divName) {
  createApp({
    data() {
      return {
        count: 0
      }
    }
  }).use(ganttastic)
    .mount(divName)
}
*/



export function gant(){
  return ganttastic
}

export function hello() {
  console.log('world')
}

export function vueRef(){
  return ref
}

export function vueCreateApp(){
  return createApp
}

/*

var runfortest = function (divName) {
  return createApp({
    data() {
      return {
        count: 0
      }
    },
    mounted() {
      this.count++
      console.log(ganttastic, this.count)
    }
  }).mount(divName)
  
}

runfortest("#gantt")
*/