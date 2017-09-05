'use strict'

advert
  .filter('thumbnail_url', function() {
    return function( input ) {
      if (isNaN(input)) {
        return input;
      } else {
        var post = _.find( adverts.thumbnails, function( thumbnail ) {
          return thumbnail.post_id = parseInt( input );
        });
        return post.thumbnail_url;
      }
    }
  })
  .filter('currency', function() {
    return function( input ) {
      var currency = 'Ar';
      if (isNaN(input)) {
        return input;
      } else {
        input = parseFloat(input);
        return input.toFixed(2).replace(/./g, function(c, i, a) {
          return i && c !== "." && ((a.length - i) % 3 === 0) ? ',' + c : c ;
      });
      }
    }
  })