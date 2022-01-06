<template>
  <div id="app">
    <Rating :star="this.star" :disabled="disabled" :maxstars="this.maxstars" :starsize="this.starsize" :hasresults="this.hasresults" :hasdescription="this.hasdescription" :ratingdescription="this.ratingdescription" />
  </div>
</template>

<script>
import Rating from "./components/Rating";

export default {
  name: "app",
  components: {
    Rating
  },
  props: {
    star: {
      type: Number,
      default: 1
    },
    ratingdescription: {
      type: Array,
      default() {
        return [
          {
            text: "Poor",
            class: "star-poor"
          },
          {
            text: "Below Average",
            class: "star-belowAverage"
          },
          {
            text: "Average",
            class: "star-average"
          },
          {
            text: "Good",
            class: "star-good"
          },
          {
            text: "Excellent",
            class: "star-excellent"
          }
        ];
      }
    },
    hasresults: {
      type: Boolean,
      default: true
    },
    hasdescription: {
      type: Boolean,
      default: true
    },
    starsize: {
      type: String,
      default: "lg" //xs/6x
    },
    maxstars: {
      type: Number,
      default: 5
    },
    disabled: {
      type: Boolean,
      default: false
    }
  }
};
</script>
