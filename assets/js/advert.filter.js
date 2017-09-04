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
  });