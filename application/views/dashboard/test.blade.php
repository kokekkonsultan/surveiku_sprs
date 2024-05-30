@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link rel="stylesheet" type="text/css"
    href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css" />
<style type="text/css">
#map {
    height: 500px;
}

/* .info {
    padding: 6px 8px;
    font: 14px/16px Arial, Helvetica, sans-serif;
    background: white;
    background: rgba(255, 255, 255, 0.8);
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    border-radius: 5px;
}

.info h4 {
    margin: 0 0 5px;
    color: #777;
} */

/* .leaflet-tooltip-left:before {
    right: 0;
    margin-right: -12px;
    border-left-color: rgba(0, 0, 0, 0.4);
}
.leaflet-tooltip-right:before {
    left: 0;
    margin-left: -12px;
    border-right-color: rgba(0, 0, 0, 0.4);
    }
.leaflet-tooltip-own {
    position: absolute;
    padding: 4px;
    background-color: rgba(0, 0, 0, 0.4);
    border: 0px solid #000;
    color: #000;
    white-space: nowrap;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    pointer-events: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.4);
} */

.legend {
    line-height: 18px;
    color: #555;
    padding: 6px 8px;
    background: white;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    border-radius: 5px;
}
.legend i {
    width: 18px;
    height: 18px;
    float: left;
    margin-right: 8px;
    opacity: 0.7;
}
</style>
@endsection

@section('content')

<div class="container-fluid">
    <div class="card card-body">
        <div id="map"></div>

    </div>
</div>

@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"></script>
<script src="http://192.168.1.105:2250/assets/files/maps/kotakab.js"></script>

<script>

</script>

<script>
var map = L.map('map').setView([-1.9801575, 119.6599062], 5);
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
	attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy;',
	subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
	maxZoom: 18
}).addTo(map);



// var info = L.control();
// info.onAdd = function(map) {
//     this._div = L.DomUtil.create('div', 'info');
//     this.update();
//     return this._div;
// };

// info.update = function(props) {
//     this._div.innerHTML = '<h4>Districts and Cities in Indonesia</h4>' + (props ?
//         '<b>' + props.name + '</b><br />Kode : ' + props.kode + '' : 'Hover over a state');
// };
// info.addTo(map);


function getColor(d) {
    if (d > 25) {
        color = '#557b9e';
    } else {
        color = '#5fa5bd';
    };
    return color;
}

// CREATE FUNCTION TO STYLE AND APPLY GET COLOR
function style(feature) {
    return {
        // apply get color
        fillColor: getColor(feature.properties.kode_provinsi),
        weight: 1,
        opacity: 1,
        color: 'white',
        dashArray: '1',
        fillOpacity: 0.7
    }
}

var geojson = L.geoJson(statesData, {
    style: style,
    onEachFeature: onEachFeature
}).addTo(map);


function highlightFeature(e) {
    var layer = e.target;
    var prop = layer.feature.properties;

    layer.setStyle({
        weight: 1,
        color: 'black',
        dashArray: '',
        fillOpacity: 0.7
    });

    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
        layer.bringToFront();
    }

    var htmlTooltip = '<div style="background:white; padding:1px 3px 1px 3px;"><b>Kota Kab : </b>' + prop.kota_kab + '<br><b>Provinsi : </b>' + prop.provinsi + '<br><b>Kota Kab : </b>' + prop.kota_kab + '<br><b>Kode Kota Kab : </b>' + prop.kode_kota_kab + '<br><b>Kode Provinsi : </b>' + prop.kode_provinsi + '</div>';

    // info.update(layer.feature.properties);
    layer.bindTooltip(htmlTooltip, {
            direction: 'right',
            permanent: false,
            sticky: true,
            offset: [10, 0],
            opacity: 1,
            className: 'leaflet-tooltip-own'
        }).openTooltip();
}


function resetHighlight(e) {
    geojson.resetStyle(e.target);
    // info.update();
}

function zoomToFeature(e) {
    map.fitBounds(e.target.getBounds());
}

function onEachFeature(feature, layer) {
    layer.on({
        mouseover: highlightFeature,
        mouseout: resetHighlight,
        click: zoomToFeature
    });

    // layer.on('click', function() {
    //     //you bind the popup here, you can acces any property of your Geojson with feature.properties.propertyname
    //     layer.bindPopup('<p>' + feature.properties.name + '</p><p>' + feature.properties.density + '</p>').openPopup();
    // });
}



var legend = L.control({
    position: 'topright'
});

legend.onAdd = function(map) {
    var div = L.DomUtil.create('div', 'info legend'),
        grades = [0, 25, 50, 75, 100],
        labels = [],
        from, to;


    for (var i = 0; i < grades.length; i++) {
        from = grades[i];
        to = grades[i + 1];

        labels.push(
            '<i style="background:' + getColor(from + 1) + '"></i> ' +
            from + (to ? '&ndash;' + to : '+'));
    }

    div.innerHTML = '<b>Districts and Cities in Indonesia</b><br>' + labels.join('<br>');
    return div;
};
legend.addTo(map);
</script>

@endsection