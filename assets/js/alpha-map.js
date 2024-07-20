jQuery(window).on("elementor/frontend/init", function () {

    elementorFrontend.hooks.addAction(
        "frontend/element_ready/alpha-google-map.default",
        function ($scope, $) {

            var mapElement = $scope.find(".alpha_map_height");

            var mapSettings = mapElement.data("settings");

            var mapStyle = mapElement.data("style");

            var alphaMapMarkers = [];

            var selectedMarker = {};

            alphaMap = newMap(mapElement, mapSettings, mapStyle);

            function newMap(map, settings, mapStyle) {
                var scrollwheel = JSON.parse(settings["scrollwheel"]);
                var streetViewControl = JSON.parse(settings["streetViewControl"]);
                var fullscreenControl = JSON.parse(settings["fullScreen"]);
                var zoomControl = JSON.parse(settings["zoomControl"]);
                var mapTypeControl = JSON.parse(settings["typeControl"]);
                var locationLat = JSON.parse(settings["locationlat"]);
                var locationLong = JSON.parse(settings["locationlong"]);
                var autoOpen = JSON.parse(settings["automaticOpen"]);
                var hoverOpen = JSON.parse(settings["hoverOpen"]);
                var hoverClose = JSON.parse(settings["hoverClose"]);
                var args = {
                    zoom: settings["zoom"],
                    mapTypeId: settings["maptype"],
                    center: { lat: locationLat, lng: locationLong },
                    scrollwheel: scrollwheel,
                    streetViewControl: streetViewControl,
                    fullscreenControl: fullscreenControl,
                    zoomControl: zoomControl,
                    mapTypeControl: mapTypeControl,
                    styles: mapStyle
                };

                if ("yes" === mapSettings.drag)
                    args.gestureHandling = "none";

                var markers = map.find(".alpha-pin");

                var map = new google.maps.Map(map[0], args);

                map.markers = [];
                var prev_infowindow = false;
                // add markers
                markers.each(function () {
                    add_marker(jQuery(this), map, autoOpen, hoverOpen, hoverClose);
                });

                return map;
            }

            function add_marker(pin, map, autoOpen, hoverOpen, hoverClose) {
                var latlng = new google.maps.LatLng(
                    pin.attr("data-lat"),
                    pin.attr("data-lng")
                ),
                    icon_img = pin.attr("data-icon"),
                    icon_hover_img = pin.attr("data-icon-active"),
                    maxWidth = pin.attr("data-max-width"),
                    customID = pin.attr("data-id"),
                    iconSize = parseInt(pin.attr("data-icon-size"));

                if (icon_img != "") {
                    var icon = {
                        url: pin.attr("data-icon")
                    };

                    if (icon_hover_img != "") {
                        icon.hover = pin.attr("data-icon-active");
                    }

                    if (iconSize) {

                        icon.scaledSize = new google.maps.Size(iconSize, iconSize);
                        icon.origin = new google.maps.Point(0, 0);
                        icon.anchor = new google.maps.Point(iconSize / 2, iconSize);
                    }
                }


                // create marker
                var marker = new google.maps.Marker({
                    position: latlng,
                    map: map,
                    icon: icon,
                    marker_id: customID
                });


                // add to array
                map.markers.push(marker);

                alphaMapMarkers.push(marker);


                // if marker contains HTML, add it to an infoWindow
                if (
                    pin.find(".alpha-map-info-title").html() ||
                    pin.find(".alpha-map-info-desc").html()
                ) {
                    // create info window
                    var infowindow = new google.maps.InfoWindow({
                        maxWidth: maxWidth,
                        content: pin.html()
                    });
                    var icon_url = marker.icon.url;
                    var icon_onHover = marker.icon.hover;
                    if (autoOpen) {
                        infowindow.open(map, marker);
                    }
                    if (hoverOpen) {
                        google.maps.event.addListener(marker, "mouseover", function () {
                            marker.setIcon(icon_onHover);
                            infowindow.open(map, marker);
                        });
                        if (hoverClose) {
                            google.maps.event.addListener(marker, "mouseout", function () {
                                marker.setIcon(icon_url);
                                infowindow.close(map, marker);
                            });
                        }
                    }
                    // show info window when marker is clicked
                    google.maps.event.addListener(marker, "click", function () {
                        if (typeof prev_infowindow !== 'undefined' && typeof selectedMarker !== 'undefined' && typeof selectedMarker.marker !== 'undefined') {
                            selectedMarker.marker.setIcon(selectedMarker.icon);
                            prev_infowindow.close();
                        }
                        marker.setIcon(icon_onHover);
                        selectedMarker.marker = marker;
                        selectedMarker.icon = icon_url;
                        infowindow.open(map, marker);
                        prev_infowindow = infowindow;
                    });
                    google.maps.event.addListener(map, "click", function (event) {
                        if (selectedMarker) {
                            selectedMarker.marker.setIcon(selectedMarker.icon);
                        }
                        infowindow.close();
                    });
                }
            }
        }
    );
});

jQuery(function () {
    jQuery("body").click(function (e) {
        let count = jQuery('.alpha-image-gallery').attr("data-count");
        count = parseInt(count);
        jQuery('.alpha-image-gallery').find("figure:nth-child(4)").append('<div style="position: absolute; color: #fff; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 16px; font-weight: 600; white-space: nowrap;">' + count + ' more </div>');
    });
});
