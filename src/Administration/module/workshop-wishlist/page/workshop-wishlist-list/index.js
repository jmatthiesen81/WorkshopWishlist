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
          inlineEdit: 'string',
          allowResize: true,
          primary: true
        },
        {
          property: 'amount',
          dataIndex: 'amount',
          label: 'amount',
          allowResize: true,
        }
      ]
    }
  },

  created() {
    this.repository = this.repositoryFactory.create('workshop_wishlist');
    this.isLoading = true;

    var criteria = new Criteria();
    criteria.addAssociation('products');

    this.repository
      .search(criteria, this.context)
      .then(results => {
        this.products = results;
        this.isLoading = false;
      })
  }




});
