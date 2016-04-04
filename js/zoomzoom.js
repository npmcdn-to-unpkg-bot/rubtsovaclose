;
(function($) {
  $.fn.zoomzoom = function() {
    return zoomzoom(this);
  };
}(jQuery));

function zoomzoom($elem) {

  if ($elem.length == 0) {
    return [];
  }

  $elem.addClass('zoomzoom');

  var estyle, istyle;


  var zoomOut = function() {


    var $img = $elem.find('img');
    $elem.attr("style", estyle);
    $img.attr("style", istyle);

    $elem.removeClass('zoomzoom-o');

    var ir = $img.width() / $img.height();
    var pr = $elem.width() / $elem.height();
    if (ir > pr) {
      $elem.removeClass("zoomzoom-v");
      $elem.addClass("zoomzoom-h");
    } else {
      $elem.removeClass("zoomzoom-h");
      $elem.addClass("zoomzoom-v");
    }

    try {
      $img.draggable('destroy');
    } catch (e) {}

  };

  var zoomIn = function() {

    var $img = $elem.find('img');

    estyle = $elem.attr("style");
    istyle = $img.attr("style");
    if (!estyle) estyle = "";
    if (!istyle) istyle = "";
    console.log("zoom: estyle: "+estyle+"; istyle: "+istyle);

    $elem.height($elem.height());
    $elem.addClass('zoomzoom-o');
    $elem.removeClass('zoomzoom-v');
    $elem.removeClass('zoomzoom-h');

    var dw = $elem.width() - $img.width();
    var dh = $elem.height() - $img.height();

    $img.css({
      'top': dh / 2,
      'left': dw / 2
    });

    $img.draggable({
      drag: function(event, ui) {
        var left = ui.position.left;
        var top = ui.position.top;
        if (left < 0) {
          ui.position.left = Math.max(dw, ui.position.left);
        } else {
          ui.position.left = 0;
        }
        if (top < 0) {
          ui.position.top = Math.max(dh, ui.position.top);
        } else {
          ui.position.top = 0;
        }
      }
    });

  }

  var zoomed = false;
  var zoomToggle = function() {
    if (zoomed) {
      zoomOut();
      zoomed = false;
    } else {
      zoomIn();
      zoomed = true;
    }
  }

  $elem.click(zoomToggle);

  return {
    zoomIn: zoomIn,
    zoomOut: zoomOut,
    zoomToggle: zoomToggle
  }
}
