var modal = $('#quick-view-modal')

const COL_HEIGHT_DESRIMINATOR = 55
var colfullh = modal.find('.col-full-height')

var image = modal.find('.image')
var zoomer = image.parent().zoomzoom()
image.load(function() {
  zoomer.zoomOut()
})

modal.on('show.bs.modal', function(event) {

  // update description
  var target = $(event.relatedTarget)
  image.attr('src', target.data('big-img'));
  modal.find('.title').text(target.data('title'));
  modal.find('.prise').text(target.data('prise'));
  modal.find('.desc').text(target.data('desc'));

  // update image
  zoomer.zoomOut();
  var colh = $(window).height() - COL_HEIGHT_DESRIMINATOR;
  colfullh.height(colh);
});

// handle history
var onpopstate = function(event) {
  var params = urlParse(document.location.hash);
  var pid = params["pid"];
  if (pid) {
    var target = $('.product-image[data-pid="' + pid + '"]')
    modal.modal('show', target)
  } else {
    modal.modal('hide');
  }
}
window.onpopstate = onpopstate
onpopstate()
