<template>
        <div class="container bootstrap snippet">

        <div class="row">
            
            <table class="table table-bordered sortable">

    <thead>

      <tr>

        <th @click="sort('alid')">No</th>

        <th @click="sort('title')">Title</th>

        <th @click="sort('url')">Url</th>

        <th @click="sort('br')">Bedrooms</th>

        <th @click="sort('cost')">Cost</th>

        <th @click="sort('location')">Location</th>

        <th @click="sort('posted')">Posted</th>

        <th @click="sort('picture')">Picture</th>
      </tr>

    </thead>

    <tbody>

      <tr v-for="listing in sortedListings" v-bind:key="listing.alid">

        <td>{{listing.alid}}</td>

        <td>{{listing.title}}</td>

        <td><a :href="listing.url" target="_blank">{{listing.url}}</a></td>

        <td>{{listing.br}}</td>

        <td>{{listing.cost}}</td>

        <td>{{listing.location}}</td>

        <td>{{listing.posted| moment}}</td>

        <td><img :src="listing.picture" /></td>

      </tr>


    </tbody>

  </table>
        </div>
    </div>

    <!-- end results-->

</template>


<script>
import _ from 'lodash';
import moment from 'moment';
import 'jquery';
import 'bootstrap/dist/css/bootstrap.min.css';

export default {
  name: 'uccl',
  components: {},
  data() {

return {
listings: [],
    currSort:'posted',
    currSortDir:'desc'

};

},
filters: {
  moment: function (date) {
    return moment.unix(date).utc().format('YYYY-MM-DD hh:mm');
  }
},
methods: {
initListings(){
    this.axios.get('/api/_uccl.php?action=i').then((resp) => {

        //if no error we're good
        if(resp.error) alert(resp.error);
    });
},
getListings(){
    this.axios.get('/api/_uccl.php?action=g').then((resp) => {

        this.listings = resp.data;
    });
},
sort:function(s) {
    
    if(s === this.currSort) {
      this.currSortDir = this.currSortDir==='asc'?'desc':'asc';
    }
    this.currSort = s;

  }
},
created: function(){},
  mounted(){
    this.initListings();
    this.getListings();
  },
  computed:{
  sortedListings:function() {
    if(this.currSort === 'alid'){ //this field was being interpeted as a string.. had to make a case for it

        return _.orderBy(this.listings, item => Number(item.alid), [this.currSortDir]);

    }
    else return _.orderBy(this.listings, [this.currSort], [this.currSortDir]);
    }
    
    //sortedListings:function() {
    //return this.listings.slice().sort((a,b) => { //.sort mutates the array have to clone first to avoid Vue getting angry :)
    //  let m = 1;
    //  if(this.currSortDir === 'desc') m = -1;
    //  if(a[this.currSort] < b[this.currSort]) return -1 * m;
    //  if(a[this.currSort] > b[this.currSort]) return 1 * m;
    //  return 0;
    //});
  //}
  
  }
}
</script>