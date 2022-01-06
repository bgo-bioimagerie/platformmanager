# Awesome Vue Star Rating
Simple and Easy Customisable Awesome Vue Star Rating using Pure Vue application without any external package.

## Installing
```
npm install --save awesome-vue-star-rating
```
## Features
1. Font icon stars — scale without loss of quality
2. Customisable ratings and rating descriptions
3. Customisable results of selected value and description
4. Customisable number of stars
5. Create read-only stars
6. Customisable Colors


## Options
 
 <table>
<thead>
<tr>
<th>Property</th>
<th>Type</th>
<th>Required</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td>star</td>
<td>Number</td>
<td>Required.</td>
<td>Default value of Star. Default value is <code>2</code></td>
</tr>
<tr>
<td>maxstars</td>
<td>Number</td>
<td>Required.</td>
<td>Default value is <code>5</code>.</td>
</tr>
<tr>
<td>hasresults</td>
<td>Boolean</td>
<td>Optional.</td>
<td>Displaying the result values. Default value is <code>true</code>.</td>
</tr>
<tr>
<td>hasdescription</td>
<td>Boolean</td>
<td>Optional.</td>
<td>Default value is <code>true</code>.</td>
</tr>
<tr>
<td>ratingdescription</td>
<td>Array</td>
<td>Required.</td>
<td>Displaying the rating values. Ex. <code>[{
          text: 'Poor',
          class: 'star-poor'
        },
        {
          text: 'Below Average',
          class: 'star-belowAverage'
        },
        {
          text: 'Average',
          class: 'star-average'
        },
        {
          text: 'Good',
          class: 'star-good'
        },
        {
          text: 'Excellent',
          class: 'star-excellent'
        }]</code></td>
</tr>
<tr>
<td>starsize</td>
<td>String</td>
<td>Optional.</td>
<td>Displaying the size of the stars. Default value is <code>lg</code>. We have list of <code>[xs,lg,1x,2x,3x,4x,5x,6x,7x,8x,9x,10x]</code> other optional values too.</td>
</tr>
<tr>
<td>disabled</td>
<td>Boolean</td>
<td>Optional.</td>
<td>Enable or disable the star selection. Default value is <code>false</code>.</td>
</tr>
</tbody>
</table>

# Usage

Step 1: import AwesomeVueStarRating in our component

```
import AwesomeVueStarRating from 'awesome-vue-star-rating'

export default {
  name: 'app',
  components: {
    AwesomeVueStarRating
  }
}
</script>
```
Step 2: Load default values to the component

```
data() {
    return {
      star: 5, // default star
      ratingdescription: [
        {
          text: 'Poor',
          class: 'star-poor'
        },
        {
          text: 'Below Average',
          class: 'star-belowAverage'
        },
        {
          text: 'Average',
          class: 'star-average'
        },
        {
          text: 'Good',
          class: 'star-good'
        },
        {
          text: 'Excellent',
          class: 'star-excellent'
        }
      ],
      hasresults: true,
      hasdescription: true,
      starsize: 'lg', //[xs,lg,1x,2x,3x,4x,5x,6x,7x,8x,9x,10x],
      maxstars: 5,
      disabled: false
    }
}
```

Step 3: Place the Rate component inside the template
```
<template>
  <div id="app">
    <AwesomeVueStarRating :star="this.star" :disabled="this.disabled" :maxstars="this.maxstars" :starsize="this.starsize" :hasresults="this.hasresults" :hasdescription="this.hasdescription" :ratingdescription="this.ratingdescription" />
  </div>
</template>
```

Step 4: Customize the star color styles like below
```
<style lang="scss">
 .star {
  color: red;
 }
 .star.active {
  color: red;
 }
 .list, .list.disabled {
  &:hover {
    .star {
      color: red !important;
    }
    .star.active {
      color: red;
    }
  }
}
</style>
```

### Compiles and hot-reloads for development
```
npm run serve
```

# Screen Shot
<img width="343" alt="Screenshot 2021-03-15 at 9 56 04 PM" src="https://user-images.githubusercontent.com/3702438/111186769-5aa39700-85d9-11eb-9708-e68fda77524d.png">
<img width="399" alt="Screenshot 2021-04-03 at 3 48 33 PM" src="https://user-images.githubusercontent.com/3702438/113475579-33334200-9494-11eb-84d5-34c7829a0e72.png">


# Demo
![Vue_Star](https://user-images.githubusercontent.com/3702438/113581801-3cafdc00-9645-11eb-9b21-0e89db833c70.gif)

## <g-emoji class="g-emoji" alias="tada" fallback-src="https://github.githubassets.com/images/icons/emoji/unicode/1f389.png">🎉 </g-emoji> [Demo Link](https://codesandbox.io/s/reverent-heyrovsky-juycp)  <g-emoji class="g-emoji" alias="tada" fallback-src="https://github.githubassets.com/images/icons/emoji/unicode/1f389.png">🎉</g-emoji>

### Compiles and minifies for production
```
npm run build
```

### Run your tests
```
npm run test
```
