(function ($) {
	const MAP_READY_MAX_TRIES = 20;
	const MAP_READY_DELAY = 150;

	const waitForGoogleMaps = () =>
		new Promise((resolve, reject) => {
			let attempts = 0;
			const timer = setInterval(() => {
				attempts += 1;
				if (
					window.google &&
					window.google.maps &&
					typeof window.google.maps.Map === 'function'
				) {
					clearInterval(timer);
					resolve(window.google.maps);
					return;
				}

				if (attempts >= MAP_READY_MAX_TRIES) {
					clearInterval(timer);
					reject(new Error('Google Maps failed to load'));
				}
			}, MAP_READY_DELAY);
		});

	const applyGalleryOverlay = ($root) => {
		$root.find('.alpha-image-gallery').each(function () {
			const $gallery = $(this);
			const remaining = parseInt($gallery.attr('data-count'), 10) || 0;

			if (remaining <= 0) {
				return;
			}

			const $figures = $gallery.find('figure');
			const $items = $figures.length ? $figures : $gallery.find('.gallery-item');
			const $target = $items.eq(3);

			if (!$target.length || $target.find('.alpha-gallery-more').length) {
				return;
			}

			$('<div class="alpha-gallery-more" />')
				.text(`${remaining} more`)
				.appendTo($target);
		});
	};

	const addMarker = ({ $pin, map, settings, state }) => {
		const lat = parseFloat($pin.attr('data-lat'));
		const lng = parseFloat($pin.attr('data-lng'));

		if (Number.isNaN(lat) || Number.isNaN(lng)) {
			return;
		}

		const iconUrl = $pin.attr('data-icon');
		const hoverIconUrl = $pin.attr('data-icon-active');
		const iconSize = parseInt($pin.attr('data-icon-size'), 10);
		let icon = null;

		if (iconUrl) {
			icon = { url: iconUrl };

			if (iconSize) {
				icon.scaledSize = new google.maps.Size(iconSize, iconSize);
				icon.origin = new google.maps.Point(0, 0);
				icon.anchor = new google.maps.Point(iconSize / 2, iconSize);
			}

			if (hoverIconUrl) {
				icon.hover = hoverIconUrl;
			}
		}

		const markerOptions = {
			position: { lat, lng },
			map,
			marker_id: $pin.attr('data-id'),
		};

		if (icon) {
			markerOptions.icon = icon;
		}

		const marker = new google.maps.Marker(markerOptions);

		const hasInfo =
			$pin.find('.alpha-map-info-title').length ||
			$pin.find('.alpha-map-info-desc').length ||
			$pin.find('.alpha-map-info-time-desc').length;

		if (!hasInfo) {
			return;
		}

		applyGalleryOverlay($pin);

		const infoWindow = new google.maps.InfoWindow({
			maxWidth: parseInt($pin.attr('data-max-width'), 10) || undefined,
			content: $pin.html(),
		});

		const defaultIconUrl = icon && icon.url ? icon.url : null;
		const activeIconUrl = icon && icon.hover ? icon.hover : null;

		if (settings.automaticOpen) {
			infoWindow.open(map, marker);
			state.activeInfo = infoWindow;
			state.activeMarker = marker;
		}

		if (settings.hoverOpen) {
			marker.addListener('mouseover', () => {
				if (activeIconUrl && marker.setIcon) {
					marker.setIcon(activeIconUrl);
				}
				infoWindow.open(map, marker);
			});

			if (settings.hoverClose) {
				marker.addListener('mouseout', () => {
					if (defaultIconUrl && marker.setIcon) {
						marker.setIcon(defaultIconUrl);
					}
					infoWindow.close();
				});
			}
		}

		marker.addListener('click', () => {
			if (state.activeMarker && state.activeMarker !== marker) {
				if (state.activeMarker.setIcon && defaultIconUrl) {
					state.activeMarker.setIcon(defaultIconUrl);
				}
				if (state.activeInfo) {
					state.activeInfo.close();
				}
			}

			if (activeIconUrl && marker.setIcon) {
				marker.setIcon(activeIconUrl);
			}

			state.activeMarker = marker;
			state.activeInfo = infoWindow;
			infoWindow.open(map, marker);
		});

		map.addListener('click', () => {
			if (state.activeMarker && defaultIconUrl && state.activeMarker.setIcon) {
				state.activeMarker.setIcon(defaultIconUrl);
			}
			if (state.activeInfo) {
				state.activeInfo.close();
			}
		});
	};

	const initMapInstance = ($scope) => {
		const $mapElement = $scope.find('.alpha_map_height');

		if (!$mapElement.length) {
			return;
		}

		const settings = $mapElement.data('settings') || {};
		const centerLat = parseFloat(settings.locationlat);
		const centerLng = parseFloat(settings.locationlong);

		if (Number.isNaN(centerLat) || Number.isNaN(centerLng)) {
			return;
		}

		const mapOptions = {
			zoom: Number(settings.zoom) || 12,
			mapTypeId: settings.maptype || 'roadmap',
			center: { lat: centerLat, lng: centerLng },
			scrollwheel: Boolean(settings.scrollwheel),
			streetViewControl: Boolean(settings.streetViewControl),
			fullscreenControl: Boolean(settings.fullScreen),
			zoomControl: Boolean(settings.zoomControl),
			mapTypeControl: Boolean(settings.typeControl),
			gestureHandling: settings.gestureHandling || 'auto',
		};

		if (settings.mapId) {
			mapOptions.mapId = settings.mapId;
		}

		if (settings.drag) {
			mapOptions.gestureHandling = 'none';
			mapOptions.draggable = false;
		}

		const mapStyle = $mapElement.data('style');
		if (mapStyle) {
			mapOptions.styles = mapStyle;
		}

		// Capture pins before Google Maps mutates the DOM.
		const $pins = $mapElement.find('.alpha-pin');

		const map = new google.maps.Map($mapElement[0], mapOptions);
		const state = {
			activeMarker: null,
			activeInfo: null,
		};

		$pins.each(function () {
			addMarker({
				$pin: $(this),
				map,
				settings: {
					automaticOpen: Boolean(settings.automaticOpen),
					hoverOpen: Boolean(settings.hoverOpen),
					hoverClose: Boolean(settings.hoverClose),
				},
				state,
			});
		});

		applyGalleryOverlay($scope);
	};

	const bootstrap = () => {
		elementorFrontend.hooks.addAction('frontend/element_ready/alpha-google-map.default', ($scope) => {
			const config = window.AlphaMapConfig || {};

			if (!config.hasApiKey) {
				if (config.missingApiKeyMessage) {
					console.warn(config.missingApiKeyMessage);
				}
				return;
			}

			const hasMapCtor =
				window.google &&
				window.google.maps &&
				typeof window.google.maps.Map === 'function';

			const ready = hasMapCtor ? Promise.resolve(window.google.maps) : waitForGoogleMaps();

			ready
				.then(() => initMapInstance($scope))
				.catch(() => {
					console.warn('Alpha Google Map: Google Maps failed to load.');
				});
		});
	};

	$(window).on('elementor/frontend/init', bootstrap);
})(jQuery);
