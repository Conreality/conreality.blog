$(function() {
  $('h1, h2, h3, h4, h5, h6').each(function() {
    var self = $(this);
    self.attr('id', self.text().replace(' ', '-'));
  });
});