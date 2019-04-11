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
          allowResize: true,
        }
      ]
    }
  },

  created() {
    window.console.log('Wishlists');
    this.repository = this.repositoryFactory.create('product');
    this.isLoading = true;

    this.repository
      .search(new Criteria(), this.context)
      .then(result => {
        console.log(result);
        this.products = result;
        this.isLoading = false;
      })
  }




});
