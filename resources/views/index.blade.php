<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fun Travelling</title>
    <link rel="icon" type="image/ico" href="{{ URL::asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/responsee.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/icons.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('owl-carousel/owl.carousel.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('owl-carousel/owl.theme.css') }}">
    <!-- CUSTOM STYLE -->
    <link rel="stylesheet" href="{{ URL::asset('css/template-style.css') }}">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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

        #roadTripDetails {
            margin-top: 70px;
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

        /* Override too complex styles that we don't need, specifically top sticky navbar */
        header {
            position: fixed;
        }

        nav > div {
            height: 60px;
        }
    </style>
    <style>
        /* Absolute Center Spinner */
        .loading {
            position: fixed;
            z-index: 999;
            height: 2em;
            width: 2em;
            overflow: show;
            margin: auto;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
        }

        /* Transparent Overlay */
        .loading:before {
            content: '';
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.3);
        }

        /* :not(:required) hides these rules from IE9 and below */
        .loading:not(:required) {
            /* hide "loading..." text */
            font: 0/0 a;
            color: transparent;
            text-shadow: none;
            background-color: transparent;
            border: 0;
        }

        .loading:not(:required):after {
            content: '';
            display: block;
            font-size: 10px;
            width: 1em;
            height: 1em;
            margin-top: -0.5em;
            -webkit-animation: spinner 1500ms infinite linear;
            -moz-animation: spinner 1500ms infinite linear;
            -ms-animation: spinner 1500ms infinite linear;
            -o-animation: spinner 1500ms infinite linear;
            animation: spinner 1500ms infinite linear;
            border-radius: 0.5em;
            -webkit-box-shadow: rgba(0, 0, 0, 0.75) 1.5em 0 0 0, rgba(0, 0, 0, 0.75) 1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) 0 1.5em 0 0, rgba(0, 0, 0, 0.75) -1.1em 1.1em 0 0, rgba(0, 0, 0, 0.5) -1.5em 0 0 0, rgba(0, 0, 0, 0.5) -1.1em -1.1em 0 0, rgba(0, 0, 0, 0.75) 0 -1.5em 0 0, rgba(0, 0, 0, 0.75) 1.1em -1.1em 0 0;
            box-shadow: rgba(0, 0, 0, 0.75) 1.5em 0 0 0, rgba(0, 0, 0, 0.75) 1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) 0 1.5em 0 0, rgba(0, 0, 0, 0.75) -1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) -1.5em 0 0 0, rgba(0, 0, 0, 0.75) -1.1em -1.1em 0 0, rgba(0, 0, 0, 0.75) 0 -1.5em 0 0, rgba(0, 0, 0, 0.75) 1.1em -1.1em 0 0;
        }

        /* Animation */

        @-webkit-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }
            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }
        @-moz-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }
            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }
        @-o-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }
            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }
        @keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }
            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        .customform .s-3 input, .customform .s-2 button#buton, .customform .s-2 input {
            min-height: 60px; !important;
        }

        #roadTripDetails {
            display: flex;
            justify-content: center;
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
        <div class="line" style="height:70px">
            <div class="text-center, s-12 l-2">
                <p class="logo"><strong>FUN</strong>&nbsp;TRAVELLING</p>
            </div>
        </div>
    </nav>
</header>
<section>
    <div id="loadingIcon"></div>
    <!-- DETAILS FORM -->
    <div id="roadTripDetails" class="s-12 m-12 l-12">
        <form class="customform" action="">
            <div class="row">
                <div class="s-3">
                    <input id= "durationInput" name="duration" placeholder="Duration of the trip (hours)" type="text" />
                </div>
                <div class="s-3">
                    <input id= "maximumStayInput" name="maximumStay" placeholder="Maximum stay (hours)" type="text" />
                </div>
                <div class="s-2">
                    <button id="buton" class="btn btn-primary">Search for a plan</button>
                </div>
            </div>
            <div class="row">
                <h6 id="sourceLocationTextInput"></h6> TO <h6 id="destinationLocationTextInput"></h6>
            </div>
        </form>
    </div>


    <!-- MAP -->
    <div id="map"></div>

    <iframe id="face" width="89" height="20" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allow="encrypted-media"></iframe>
</section>
<script type="text/javascript" src="{{ URL::asset('js/responsee.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('owl-carousel/owl.carousel.js') }}"></script>
<script src="{{ URL::asset('leaflet/leaflet-search.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        let sUrl = window.location.href;
        let url = "https://www.facebook.com/plugins/share_button.php?href="+encodeURIComponent(sUrl)+"&layout=button_count&size=small&mobile_iframe=true&width=89&height=20&appId";
        document.getElementById('face').setAttribute('src', url);
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
        });

        L.tileLayer( 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            subdomains: ['a', 'b', 'c']
        }).addTo( map );

        var sourceOrDestination = 'source';
        var startIcon = L.icon({
            iconUrl: 'start_black.png',
            iconSize: [32, 32], // size of the icon
        });
        var finishIcon = L.icon({
            iconUrl: 'end.png',
            iconSize: [32, 32], // size of the icon
        });

        map.on('click', function(e) {
            //pune markerul
            let pickedIcon = '';
            if (sourceOrDestination === 'source') {
                pickedIcon = startIcon;
                sourceOrDestination = 'destination';
                searchHandler.options.sourceLocationCoordinates = {lat:e.latlng.lat, lng:e.latlng.lng}
            }
            else {
                pickedIcon = finishIcon;
                sourceOrDestination = 'source';
                searchHandler.options.destinationLocationCoordinates = {lat:e.latlng.lat, lng:e.latlng.lng}
            }
            L.marker([e.latlng.lat, e.latlng.lng], {icon: pickedIcon}).addTo(map);
            map.setView([e.latlng.lat, e.latlng.lng], 10);
            searchHandler.options.sourceOrDestinationOption = sourceOrDestination;
        });

        var popup = L.popup();
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

        const urlParams = new URLSearchParams(window.location.search);
        const latS = urlParams.get('latS');
        const lonS = urlParams.get('lonS');
        const lat = urlParams.get('lat');
        const lon = urlParams.get('lon');
        const duration = urlParams.get('duration');
        const country = urlParams.get('country');
        if(latS !== null && lonS !== null && lat !== null && lon !== null && duration !== null && country != null) { //just load site here
            // alert("Fac ceva");
            searchHandler.options.sourceLocationCoordinates =  {lat:latS, lng:lonS};
            searchHandler.options.destinationLocationCoordinates =  {lat:lat, lng:lon};
            $('#durationInput').val(duration);
            ia_harta(tara = country);
            // return; //optional, depinde cum vrem sa o gandim
        }

        $('#buton').on('click', function (e) {
            e.preventDefault();
            var tara = $('#sourceLocationTextInput').val().split(',');
            tara = tara[tara.length - 1];
            tara = tara.substr(1);
            var data = {
                start: searchHandler.options.sourceLocationCoordinates,
                finish: searchHandler.options.destinationLocationCoordinates,
                duration: $('#durationInput').val(),
                country: tara
            };
            const urlul = '?latS=' + data.start.lat + '&lonS=' + data.start.lng + '&lat=' + data.finish.lat + '&lon=' + data.finish.lng + '&duration=' + data.duration + '&country=' + data.country;
            history.pushState(null, '', urlul);
            ia_harta(tara);
        });

        function ia_harta(tara)
        {
            let sUrl = window.location.href;
            let url = "https://www.facebook.com/plugins/share_button.php?href="+encodeURIComponent(sUrl)+"&layout=button_count&size=small&mobile_iframe=true&width=89&height=20&appId";
            document.getElementById('face').setAttribute('src', url);
            //alert("url : "+url);
            var data = {
                start: searchHandler.options.sourceLocationCoordinates,
                finish: searchHandler.options.destinationLocationCoordinates,
                duration: $('#durationInput').val(),
                country: tara
            };
            //start loading icon
            $('#loadingIcon').toggleClass('loading');

            // STERGE TOATE datele pt a reinitializa
            searchHandler.options.sourceLocationCoordinates = {lat:null, lng:null};
            searchHandler.options.sourceLocationText = null;
            searchHandler.options.destinationLocationCoordinates = {lat:null, lng:null};
            searchHandler.options.destinationLocationText = null;
            searchHandler.options.sourceOrDestinationOption = 'source';
            sourceOrDestination = 'source';
            //sterge si toate marcherele si tot
            map.eachLayer(function (layer) {
                map.removeLayer(layer);
            });
            //readuga primul strat
            L.tileLayer( 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                subdomains: ['a', 'b', 'c']
            }).addTo( map );

            //continua cu request-ul
            $.ajax({
                url: 'getRoute?latS=' + data.start.lat + '&lonS=' + data.start.lng + '&lat=' + data.finish.lat + '&lon=' + data.finish.lng + '&duration=' + data.duration + '&country=' + data.country,
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
                    var polyline = L.polyline(latlngs, {color: 'red'}).addTo(map);
                    // zoom the map to the polyline
                    map.fitBounds(polyline.getBounds());

                    var electricStationIcon = L.icon({
                        iconUrl: 'electric-station.png',

                        iconSize: [32, 32], // size of the icon
                    });

                    for(let i = 0; i < waypoints.length; i++) {
                        let power = waypoints[i].power_left;
                        let baterry_class = '';
                        if (power > 75)
                            baterry_class = 'fa fa-battery-full';
                        else if(power > 40)
                            baterry_class = 'fa fa-battery-half';
                        else
                            baterry_class = 'fa fa-battery-quarter';

                        let initial_time = waypoints[0].elapsed_time;
                        let last_time = waypoints[waypoints.length - 1].elapsed_time;
                        let timeBetween = last_time - initial_time;
                        let time = waypoints[i].elapsed_time;
                        let time_class = '';
                        if (time/timeBetween > 0.75)
                            time_class = 'fa fa-hourglass-end';
                        else if(time/timeBetween > 0.4)
                            time_class = 'fa fa-hourglass-half';
                        else
                            time_class = 'fa fa-hourglass-start';

                        if (waypoints[i].charging === 1) { //   statie electrica, ii pun si icon separat
                            L.marker([waypoints[i].lat, waypoints[i].lon], {icon: electricStationIcon}).on('click', function(e1) {
                                popup
                                    .setLatLng(e1.latlng)
                                    .setContent(waypoints[i].name + '&nbsp;&nbsp;<i class="' + baterry_class + '"></i>'+
                                    power.toFixed(2) + '&nbsp;&nbsp;<i class="' +
                                    time_class + '"></i>' + time.toFixed(2))
                                    .openOn(map);
                            }).addTo(map);
                        }
                        else { //waypoint obisnuit
                            L.marker([waypoints[i].lat, waypoints[i].lon]).on('click', function(e1) {
                                popup
                                    .setLatLng(e1.latlng)
                                    .setContent(waypoints[i].name + '&nbsp;&nbsp;<i class="' + baterry_class + '"></i>'+
                                        power.toFixed(2) + '&nbsp;&nbsp;<i class="' +
                                        time_class + '"></i>' + time.toFixed(2))
                                    .openOn(map);
                            }).addTo(map);
                        }
                    }

                    //stop loading icon
                    $('#loadingIcon').toggleClass('loading');
                }
            });
        }

    });
</script>
</body>
</html>
