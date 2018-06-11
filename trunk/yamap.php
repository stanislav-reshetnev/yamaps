<?php
/**
 * Plugin Name: YaMaps for Wordpress
 * Description: Yandex Map integration
 * Plugin URI:  www.yhunter.ru/portfolio/dev/yamaps/
 * Author URI:  www.yhunter.ru
 * Author:      yhunter
 * Version:     0.3.4
 *
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yamaps
 * Domain Path: /languages/
 *
 */

$maps_count = 0;

// Test for the first time content and single map (WooCommerce and other custom posts)
$count_content = 0;

add_filter('the_content', 'tutsplus_the_content');
function tutsplus_the_content( $content ) {
	global $count_content;
	$count_content++;

	return $content;
}

/**
 * Return HTML code for the plugin.
 *
 * Attributes parameter may contain:
 * <pre>
 * * icon    => (string) Icon type
 * * name    => (string) Name of the place
 * * color   => (string) Icon color
 * * coord   => (string) Geo coordinates (longitude, latitude)
 * * balloon => (string) Balloon content (if given)
 * </pre>
 *
 * @see https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/ Icons types
 * @param array $attributes Attributes
 * @return string
 */
function yaplacemark_func($attributes) {
	$attributes = shortcode_atts(array(
		'coord'   => '',
		'name'    => '',
		'color'   => 'blue',
		'icon'    => 'islands#dotIcon',
		'balloon' => '',
	), $attributes);

	$attr_icon = trim($attributes['icon']);
	$attr_name = $attributes['name'];

	if (
		($attr_icon === 'islands#blueStretchyIcon')
		|| ($attr_icon === 'islands#blueIcon')
		|| ($attr_icon === 'islands#blueCircleIcon')
	) {
		$ya_hint_content = '';
		$ya_icon_content = $attr_name;
	}
	else {
		$ya_hint_content = $attr_name;
		$ya_icon_content = '';
	}

	$balloon_code = '';
	if (!empty($attributes['balloon'])) {
		$balloon_code = 'balloonContent: "' . str_replace(["\n", "\r"], '', $attributes['balloon']) . '",';
	}

  $yaplacemark = sprintf(
  	'yamaps_geo_coll.add(new ymaps.Placemark('
			. '[%1$s], '
			. '{ hintContent: "%2$s", iconContent: "%3$s", %4$s }, '
			. '{ preset: "%5$s", iconColor: "%6$s" }));',
		$attributes['coord'],	/* #1 */
		$ya_hint_content,			/* #2 */
    $ya_icon_content,			/* #3 */
    $balloon_code,				/* #4 */
    $attributes['icon'],	/* #5 */
    $attributes['color']	/* #6 */
  );

	return $yaplacemark;
}

function yamap_func($attributes, $content){
	global $yacontrol_count;
	global $maps_count;
	global $count_content;

	$attributes = shortcode_atts(array(
		'center'     => '55.7532,37.6225',
		'zoom'       => '12',
		'type'       => 'map',
		'height'     => '20rem',
		'controls'   => '',
		'scrollzoom' => '1',
		'auto_zoom'  => '0',
	), $attributes);
	$yacontrol_count = 0;

	$yamap_controls = str_replace(';', '", "', $attributes['controls']);

	if (!empty(trim($yamap_controls))) {
		$yamap_controls = '"' . $yamap_controls . '"';
	}

  $yamap_code = '';
	if (!$maps_count) { // Test for first time content and single map
		$yamap_code =
			'<script src="https://api-maps.yandex.ru/2.1/?lang=' . get_locale() . '" type="text/javascript"></script>' . "\n";
	}

	$yamap_code .=
		sprintf(
			'
						<script type="text/javascript">
                        ymaps.ready(init);
                 
                        function init () {
                            var myMap%3$d = new ymaps.Map("yamap%3$d", {
                                   center: [%4$s],
                                    zoom: %5$s,
                                    type: "%6$s",
                                    controls: [%7$s] 
                            });
                            var yamaps_geo_coll = new ymaps.GeoObjectCollection({});
                             
                            %1$s
                            %2$s
                             
                            myMap%3$d.geoObjects.add(yamaps_geo_coll);
                            %9$s
                        }
            </script>
					  <div
						  class="mist"
						  id="yamap%3$d" 
						  style="position: relative; min-height: %8$s; margin-bottom: 1rem;"> 	
					  </div>
			',
      do_shortcode(str_replace('&nbsp;', '', $content)),	/* #1 */
			empty($attributes['scrollzoom']) ? 'myMap' . $maps_count . ".behaviors.disable('scrollZoom');" : '',	/* #2 */
			$maps_count,	/* #3 */
			$attributes['center'],	/* #4 */
      $attributes['zoom'],	/* #5 */
      $attributes['type'],	/* #6 */
      $yamap_controls,	/* #7 */
      $attributes['height'],	/* #8 */
			empty($attributes['auto_zoom']) ? '' : 'myMap' . $maps_count . '.setBounds(yamaps_geo_coll.getBounds());' /* #9 */
		);

	if ($count_content >= 1) {
		$maps_count++;
	}

	return $yamap_code;
}

add_shortcode('yaplacemark', 'yaplacemark_func');
add_shortcode('yamap', 'yamap_func');
add_shortcode('yacontrol', 'yacontrol_func');

function yamaps_plugin_load_plugin_textdomain() {
    load_plugin_textdomain('yamaps', FALSE, basename( dirname( __FILE__ ) ) . '/languages/');
}
add_action('plugins_loaded', 'yamaps_plugin_load_plugin_textdomain');

/**
 * Add map button.
 *
 * @param array $plugin_array Current plugins
 * @return array
 */
function yamap_plugin_scripts($plugin_array)
{
	// Plugin localization
	wp_register_script('yamap_plugin', plugin_dir_url(__FILE__) . 'js/localization.js');
	wp_enqueue_script('yamap_plugin');

	$lang_array	 = array(
		'YaMap'          => __('Map', 'yamaps'),
		'AddMap'         => __('Add map', 'yamaps'),
		'MarkerTab'      => __('Placemark', 'yamaps'),
		'MapTab'         => __('Map', 'yamaps'),
		'MarkerIcon'     => __('Icon', 'yamaps'),
		'BlueOnly'       => __('Blue only', 'yamaps'),
		'MarkerUrl'      => __('Link', 'yamaps'),
		'MarkerUrlTip'   => __('Placemark hyperlink url', 'yamaps'),
		'MapHeight'      => __('Map height', 'yamaps'),
		'MarkerName'     => __('Placemark name', 'yamaps'),
		'MarkerNameTip'  => __('Text for hint or icon content', 'yamaps'),
		'MapControlsTip' => __('Use the links below', 'yamaps'),
		'MarkerCoord'    => __('Сoordinates', 'yamaps'),
		'MapControls'    => __('Map controls', 'yamaps'),
		'type'           => __('Map type', 'yamaps'),
		'zoom'           => __('Zoom', 'yamaps'),
		'ScrollZoom'     => __('Wheel zoom', 'yamaps'),
		'search'         => __('Search', 'yamaps'),
		'route'          => __('Route', 'yamaps'),
		'ruler'          => __('Ruler', 'yamaps'),
		'traffic'        => __('Traffic', 'yamaps'),
		'fullscreen'     => __('Full screen', 'yamaps'),
		'geolocation'    => __('Geolocation', 'yamaps'),
		'MarkerColor'    => __('Marker color', 'yamaps'),
	);

	wp_localize_script('yamap_plugin', 'yamap_object', $lang_array);

	// Enqueue TinyMCE plugin script with its ID.
	$plugin_array["yamap_plugin"] = plugin_dir_url(__FILE__) . "js/btn.js";

	return $plugin_array;
}


add_filter("mce_external_plugins", "yamap_plugin_scripts");

function register_buttons_editor($buttons)
{
    //register buttons with their id.
    array_push($buttons, "yamap");

    return $buttons;
}

add_filter("mce_buttons", "register_buttons_editor");
