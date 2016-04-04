/* LANDING */

// animate navbar-cover
var $nav = $('nav');
$(window).scroll(function() {
  if ($(window).scrollTop() == 0) {
    $nav.addClass('navbar-cover');
  } else if ($(window).scrollTop() > 0) {
    $nav.removeClass('navbar-cover');
  }
});

$('body').scrollspy({
  target: '#navbar'
})
