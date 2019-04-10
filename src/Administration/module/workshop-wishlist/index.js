import { Module } from 'src/core/shopware';
import './page/workshop-wishlist-list';

Module.register('workshop-wishlist', {
  type: 'plugin',
  name: 'Bundle',
  description: 'Bundle products',
  version: '1.0.0',
  targetVersion: '1.0.0',
  color: '#ff3d58',
  icon: 'default-shopping-paper-bag-product',

  routes: {
    list: {
      component: 'workshop-wishlist-list',
      path: 'list'
    },
  },

  navigation: [{
    id: 'workshop-wishlist-list',
    label: 'Wishlists',
    color: '#ff3d58',
    path: 'workshop.wishlist.list',
    icon: 'default-shopping-paper-bag-product',
    position: 100
  }]
});
