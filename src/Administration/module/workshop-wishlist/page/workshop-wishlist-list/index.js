import {Component, Mixin} from 'src/core/shopware'
import Criteria from 'src/core/data-new/criteria.data'
import template from './workshop-wishlist-list.html.twig'

Component.register('workshop-wishlist-list', {

  template,

  mixins: [
    Mixin.getByName('listing')
  ],

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
          inlineEdit: 'string',
          allowResize: true,
          primary: true
        },
        {
          property: 'wishlistCount',
          //dataIndex: 'wishlistCount',
          label: 'Count',
          allowResize: true,
        }
      ]
    }
  },

  methods: {
    getList() {
      this.repository = this.repositoryFactory.create('product');
      this.isLoading = true;

      let criteria = new Criteria();
      criteria.addAssociation('wishlists');

      // adds a negated filter for wishlist.id
      criteria.addFilter(
          Criteria.not('AND', [Criteria.equals('product.wishlists.id', null)])
      );

      criteria.addAggregation({
        type: 'count',
        name: 'wishlistCount',
        field: 'product.wishlists.id',
        groupByFields: ['id']
      });

      this.repository
      .search(criteria, this.context)
      .then(results => {
        let aggregations = results.aggregations.wishlistCount;

        aggregations = aggregations.reduce((accumulator, item) => {
          accumulator[item.key.id] = item.count;
          return accumulator;
        }, {});

        results.forEach((item) => {
          item.wishlistCount = aggregations[item.id];
        });

        this.products = results;
        this.isLoading = false;
      })
    }
  },

  created() {
    this.getList();
  }
});
