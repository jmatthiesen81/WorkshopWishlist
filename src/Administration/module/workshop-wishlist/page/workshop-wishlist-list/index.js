import {Component} from 'src/core/shopware'
import Criteria from 'src/core/data-new/criteria.data'
import template from './workshop-wishlist-list.html.twig'

Component.register('workshop-wishlist-list', {

  template,

  inject: [
      'repositoryFactory',
      'context'
  ],

  data() {
    return {
      repository: null,
      products: null,
      isLoading: false,
    };
  },

  computed: {
    columns() {
      return [
        {
          property: 'name',
          dataIndex: 'name',
          label: 'Name',
          allowResize: true,
          primary: true
        },
        {
          property: 'wishlistCount',
          dataIndex: 'wishlistCount',
          label: 'Count',
          allowResize: true,
        }
      ]
    }
  },

  created() {
    this.repository = this.repositoryFactory.create('product');
    this.isLoading = true;

    var criteria = new Criteria();
    criteria.addAssociation('wishlists');

    criteria.addAggregation({type: 'count', name: 'wishlistCount', field: 'product.wishlists.id', groupByFields: ['id']});

    this.repository
      .search(criteria, this.context)
      .then(results => {
        console.log(results.aggregations);

        let aggregations = results.aggregations.wishlistCount;

        aggregations = aggregations.reduce((accumulator, item) => {
          accumulator[item.key.id] = item.count;
          return accumulator;
        }, {});

        console.log(aggregations);

        results.forEach((item) => {
            const id = item.id;
            const value = aggregations[id];

            item.wishlistCount = value;
        });
        this.products = results;
        this.isLoading = false;
      })
  }
});
