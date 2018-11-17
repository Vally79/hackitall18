<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fun Travelling</title>
    <link rel="stylesheet" href="{{ URL::asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/responsee.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/icons.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('owl-carousel/owl.carousel.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('owl-carousel/owl.theme.css') }}">
    <!-- CUSTOM STYLE -->
    <link rel="stylesheet" href="{{ URL::asset('css/template-style.css') }}">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800&subset=latin,latin-ext' rel='stylesheet' type='text/css'>

    <link rel="stylesheet" href="{{ URL::asset('leaflet/leaflet.css') }}" />
    <script src="{{ URL::asset('leaflet/leaflet.js') }}"></script>

    <link rel="stylesheet" href="{{ URL::asset('leaflet/leaflet-search.css') }}" />
    {{--<link rel="stylesheet" href="{{ URL::asset('leaflet/search-style.css') }}" />--}}
    <style>
        .search-input {
            font-family:Courier
        }
        .search-input,
        .leaflet-control-search {
            max-width:400px;
        }
    </style>

    <script type="text/javascript" src="{{ URL::asset('js/jquery-1.8.3.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/template-scripts.js') }}"></script>

    <style>
        #map {
            width: 100%;
            height: 500px;
            border: 1px solid #AAA;
            position: relative;
            top: 0px;
        }

        #first-block {
            margin-top: 0px;
        }

        #roadTripDetails {
            margin-top: 90px;
            margin-left: 50px;
            width: 90%;
        }

        #roadTripDetails .s-2 {
            margin-right: 20px;
        }

        #roadTripDetails .s-3 {
            margin-right: 20px;
        }

        #roadTripDetails > h3 {
            text-align: center;
            font-size: 1.5rem;
        }

    </style>
</head>
<body class="size-1140">
<!-- PREMIUM FEATURES BUTTON -->
{{--
<a target="_blank" class="hide-s" href="../template/onepage-premium-template/" style="position:fixed;top:130px;right:-14px;z-index:10;"><img src="{{ URL::asset('img/premium-features.png') }}" alt=""></a>
--}}
<!-- TOP NAV WITH LOGO -->
<header>
    <nav>
        <div class="line">
            <div class="s-12 l-2">
                <p class="logo"><strong>FUN</strong>&nbsp;TRAVELLING</p>
            </div>
            <div class="top-nav s-12 l-10">
                <p class="nav-text">Custom menu text</p>
                <ul class="right">
                    <li class="active-item"><a href="#carousel">Home</a></li>
                    <li><a href="#about-us">About Us</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<section>
    <!-- DETAILS FORM -->
    <div id="roadTripDetails" class="s-12 m-12 l-12">
        <h3>Your road trip details</h3>
        <form class="customform" action="">
            <div class="s-3">
                <input name="source" id="sourceLocationTextInput" placeholder="Source" title="Your e-mail" type="text" disabled />
            </div>
            <div class="s-3">
                <input name="destination" id="destinationLocationTextInput" placeholder="Destination" title="Your name" type="text" disabled />
            </div>
            <div class="s-2">
                <input id= "durationInput" name="duration" placeholder="Duration of the trip" title="Your name" type="text" />
            </div>
            <div class="s-2">
                <button id="buton" class="btn btn-primary">Search a plan</button>
            </div>
        </form>
    </div>
    <!-- MAP -->
    <div id="map"></div>
</section>
<!-- FOOTER -->
<footer>
    <div class="line">
        <div class="s-12 l-6">
            <p>Copyright 2018, NoClue team</p>
        </div>
        <div class="s-12 l-6">
            <a class="right" href="http://www.myresponsee.com" title="Responsee - lightweight responsive framework">Design and coding<br> by NoClue Team</a>
        </div>
    </div>
</footer>
<script type="text/javascript" src="{{ URL::asset('js/responsee.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('owl-carousel/owl.carousel.js') }}"></script>
<script src="{{ URL::asset('leaflet/leaflet-search.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        var theme_slider = $("#owl-demo");
        var owl = $('#owl-demo');
        owl.owlCarousel({
            nav: false,
            dots: true,
            items: 1,
            loop: true,
            autoplay: true,
            autoplayTimeout: 6000
        });
        var owl = $('#owl-demo2');
        owl.owlCarousel({
            nav: true,
            dots: false,
            items: 1,
            loop: true,
            navText: ["&#xf007","&#xf006"],
            autoplay: true,
            autoplayTimeout: 4000
        });

        // Custom Navigation Events
        $(".next-arrow").click(function() {
            theme_slider.trigger('next.owl');
        })
        $(".prev-arrow").click(function() {
            theme_slider.trigger('prev.owl');
        })

        //////////////////////////////////////////////////////////////////////////
        ///////// ADDED BY US /////////
        //////////////////////////////////////////////////////////////////////////
        // See post: http://asmaloney.com/2014/01/code/creating-an-interactive-map-with-leaflet-and-openstreetmap/

        var map = L.map( 'map', {
            center: [20.0, 5.0],
            minZoom: 2,
            zoom: 2
        })

        L.tileLayer( 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            subdomains: ['a', 'b', 'c']
        }).addTo( map )

        /*
        var myURL = jQuery( 'script[src$="leaf-demo.js"]' ).attr( 'src' ).replace( 'leaf-demo.js', '' )

        var myIcon = L.icon({
            iconUrl: myURL + 'images/pin24.png',
            iconRetinaUrl: myURL + 'images/pin48.png',
            iconSize: [29, 24],
            iconAnchor: [9, 21],
            popupAnchor: [0, -14]
        })

        for ( var i=0; i < markers.length; ++i )
        {
            L.marker( [markers[i].lat, markers[i].lng], {icon: myIcon} )
                .bindPopup( '<a href="' + markers[i].url + '" target="_blank">' + markers[i].name + '</a>' )
                .addTo( map );
        }*/

        {{--
        var greenIcon = L.icon({
            iconUrl: '{{ URL::asset('img/leaf-green.png') }}',
            shadowUrl: '{{ URL::asset('img/leaf-shadow.png') }}',

            iconSize:     [38, 95], // size of the icon
            shadowSize:   [50, 64], // size of the shadow
            iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
            shadowAnchor: [4, 62],  // the same for the shadow
            popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
        });

        L.marker([51.5, -0.09], {icon: greenIcon}).addTo(map);
        --}}

        var popup = L.popup();

        function onMapClick(e) {
            popup
                .setLatLng(e.latlng)
                .setContent("You clicked the map at " + e.latlng.toString())
                .openOn(map);
        }

        map.on('click', onMapClick);

        a = 0;
        var searchHandler;
        map.addControl( searchHandler = new L.Control.Search({
            url: 'http://nominatim.openstreetmap.org/search?format=json&q={s}',
            jsonpParam: 'json_callback',
            propertyName: 'display_name',
            propertyLoc: ['lat','lon'],
            marker: L.circleMarker([0,0],{radius:30}),
            autoCollapse: true,
            autoType: true,
            minLength: 2,
            zoom: 10
        }) );


        $('#buton').on('click', function (e) {
            e.preventDefault();
            var data = {
                start: JSON.parse($('#sourceLocationTextInput').val()),
                finish: JSON.parse($('#destinationLocationTextInput').val()),
                duration: $('#durationInput').val(),
            };

            $.ajax({
                url: 'getRoute?latS=' + data.start.lat + '&lonS=' + data.start.lng + '&lat=' + data.finish.lat + '&lon=' + data.finish.lng + '&duration=' + data.duration,
                method: 'get',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    // create a red polyline from an array of LatLng points
                    var waypoints = response.waypoints;
                    response = response.route;
                    var latlngs = [[response[0].lat, response[0].lon]];
                    for (var i = 1 ; i < response.length ; i++) {
                        latlngs.push([response[i].lat, response[i].lon]);
                    }
                    console.log(latlngs);
                    var polyline = L.polyline(latlngs, {color: 'red'}).addTo(map);
                    // zoom the map to the polyline
                    map.fitBounds(polyline.getBounds());

                    var electricStation = L.icon({
                        iconUrl: 'electric-station.png',

                        iconSize: [32, 32], // size of the icon
                    });

                    for(var i = 1; i < waypoints.length; i++)
                        L.marker([waypoints[i].lat, waypoints[i].lon]).addTo(map);
                }
            });
        });

        //L.marker([46, 26]).addTo(map);
        //map.setView([46, 26], 10);
    });
</script>
</body>
</html>
