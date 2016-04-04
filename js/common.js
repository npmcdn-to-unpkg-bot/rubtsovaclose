// MASONRY

$(function() {

  $('.masonry').each(function() {

    var $self = $(this);
    var $img = $self.find('img');

    var _masonry = function() {
      $self.masonry({
        itemSelector: '.masonry-item',
        percentPosition: true
      });
    }

    var imgl = $img.length

    if (imgl == 0) {
      _masonry()
      return
    }

    var c = 0
    $img.one('load', function() {
      if (c == -1) return
      c++
      if (c == imgl) {
        console.log('masonry: loaded')
        _masonry()
        c = -1
      }
    }).each(function() {
      if (this.complete) $(this).trigger('load')
    })

    setTimeout(function() {
      if (c == -1) return
      console.log('masonry: timeout')
      _masonry()
      c = -1
    }, 5000)

  })
})


// HISTORY SUPPORT

function getLocation() {
  var loc = window.location.toString();
  var idx = loc.indexOf("#");
  return idx > 0 ? loc.substr(0, idx) : loc;
}

$('*[data-href]').click(function() {
  var href = $(this).data('href');
  history.pushState({}, '', getLocation() + href);
});


// SMOOTH SCROLL

$(function() {
  $('a[href*="#"]:not([href="#"])').click(function() {
    if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
      if (target.length) {
        $('html, body').animate({
          scrollTop: target.offset().top
        }, 1000);
        return false;
      }
    }
  });
});


// UTILS

// usage:
// - urlParse(location.search)
// - urlParse(location.hash)
function urlParse(p) {
  var p = p.substring(1).split("&");
  var l = p.length;
  var r = new Object();
  for (var i = 0; i < l; i++) {
    var pair = p[i].trim().split("=");
    var f = pair[0];
    if (f) r[f] = pair[1];
  }
  return r;
};
