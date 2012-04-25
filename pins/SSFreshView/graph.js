var svgdoc = "";
var cible = "";

function init(evt) {
  svgdoc = evt.target.ownerDocument
  tooltip = svgdoc.getElementById("tooltip")
}

function path_show(evt) {
  cible = evt.target
  id = cible.getAttribute("id")
  label = svgdoc.getElementById("label_"+id)
  tooltip_text = svgdoc.getElementById("tooltip_text")
  tooltip.setAttributeNS(null,"transform","translate("+(parseInt(label.getAttributeNS(null,"x"))-40)+","+(parseInt(label.getAttributeNS(null,"y"))-23)+")")
  tooltip.setAttributeNS(null,"visibility","visible")
  tooltip_text.firstChild.data = label.firstChild.data
}

function path_hide(evt) {
  tooltip.setAttributeNS(null,"visibility","hidden")
}