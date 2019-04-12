import EntityHydrator from 'shopware/core/data-new/EntityHydrator';

EntityHydrator.hydrateSearchResult = function(route, entityName, response, context, criteria) {
  const collection = this.hydrate(route, entityName, response.data, context, criteria);
  console.log('new function');
  return new SearchResult(
      route,
      entityName,
      collection.elements,
      response.data.meta.total,
      criteria,
      context,
      response.data.aggregations,
      this.view
  );
};
