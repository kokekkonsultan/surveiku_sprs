@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link rel="stylesheet" type="text/css" href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css"/>
<style type="text/css">
#mapid {
    height: 500px;
}
</style>
@endsection

@section('content')

<div class="container-fluid">
    <div class="row mt-5">
        <div class="col-md-3">
            @include('manage_survey/menu_data_survey')
        </div>
        <div class="col-md-9">

            <div class="card card-body">
                <div id="mapid"></div>


                <button class="btn btn-primary mt-5" type="button" onclick="getLocation()">Aktifkan Lokasi</button>
                <!-- <p id="demo"></p> -->

                <!-- <div class="form-group">
                    <input class="form-control" value="" id="lat" placeholder="masukan inputan anda">
                    <input class="form-control" value="" id="lng" placeholder="masukan inputan anda">
                </div> -->

            </div>

        </div>
    </div>
</div>

@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA1MgLuZuyqR_OGY3ob3M52N46TDBRI_9k&callback=initia"></script>

<script>
var x = document.getElementById("demo");
var map = L.map('mapid').setView([-7.250445, 112.768845], 5);
L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
    attribution: 'Map &copy; <a href="https://maps.google.com/">Google Maps</a>',
    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
    maxZoom: 18,
}).addTo(map);


function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError);
    } else {
        alert("Geolocation tidak didukung oleh browser ini.");
        // x.innerHTML = "Geolocation tidak didukung oleh browser ini.";
    }
}

function showError(error){
    switch(error.code){
        case error.PERMISSION_DENIED:
            alert("User denied the request for Geolocation.");
        break;
        case error.POSITION_UNAVAILABLE:
            alert("Location information is unavailable.");
        break;
        case error.TIMEOUT:
            alert("The request to get user location timed out.");
        break;
        case error.UNKNOWN_ERROR:
            alert("An unknown error occurred.");
        break;
    }
}

function showPosition(position) {
    var lat = position.coords.latitude;
    var lng = position.coords.longitude;

    // console.log(lat);
    // console.log(lng);

    // document.getElementById("lat").value = lat;
    // document.getElementById("lng").value = lng;


    displayLocation(lat,lng);
    // ajax_save(lat,lng);
}

function displayLocation(lat, lng){
    var geocoder;
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(lat, lng);

    geocoder.geocode(
        {'latLng': latlng}, 
        function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    var add = results[0].formatted_address ;
                    var value = add.split(",");

                    count = value.length;
                    negara = value[count-1];
                    provinsi = value[count-2];
                    kota = value[count-3];
                    kecamatan = value[count-4];
                    kelurahan = value[count-5];
                    jalan = value[count-6];

                    // console.log(value);
                    // console.log(negara);
                    // console.log(provinsi);
                    // console.log(kota);
                    // console.log(kecamatan);
                    // console.log(kelurahan);
                    // console.log(jalan);


                    // ====================================== Marker In Maps
                    L.marker([lat, lng]).addTo(map).bindPopup(kota).openPopup();;
                    L.circle([lat, lng], 500).addTo(map);
                    map.locate({
                        watch: true,
                        setView: true,
                        maxZoom: 15
                    });
                    // ====================================== End Marker In Maps


                    Swal.fire({
                        icon: 'info',
                        title: 'Informasi Lokasi',
                        html: add + '<br>' + lat + ', ' + lng,
                        confirmButtonColor: '#8950FC',
                        confirmButtonText: 'Baik, Terimakasih',
                    });
                } else  {
                    alert("Alamat tidak terdeteksi.");
                }
            }
            else {
                alert("Geocoder failed due to: " + status);
            }
        }
    );
}


// function ajax_save(lat,lng) {

    // $.ajax({
    //     url: 'ajax.php',
    //     type: 'POST',
    //     data: {
    //         lat: lat,
    //         lng: lng
    //     },
    //     cache: false,
    //     beforeSend: function() {
    //         $('.tombolSimpanDefault').attr('disabled', 'disabled');
    //         $('.tombolSimpanDefault').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

    //         Swal.fire({
    //             title: 'Memproses data',
    //             html: 'Mohon tunggu sebentar. Sistem sedang menyiapkan request anda.',
    //             allowOutsideClick: false,
    //             onOpen: () => {
    //                 swal.showLoading()
    //             }
    //         });

    //     },
    //     complete: function() {
    //         $('.tombolSimpanDefault').removeAttr('disabled');
    //         $('.tombolSimpanDefault').html('Aktifkan Lokasi');
    //     },
    //     error: function(e) {
    //         Swal.fire(
    //             'Error !',
    //             e,
    //             'error'
    //         )
    //     },
    //     success: function(data) {
    //         if (data.sukses) {
    //             toastr["success"]('Data berhasil disimpan');
    //             // table.ajax.reload();
    //             window.setTimeout(function() {
    //                 location.reload()
    //             }, 2500);
    //         }
    //     }
    // });
    // return false;
// }
</script>

@endsection