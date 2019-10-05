<?php
# Copyright �2018 - Aaron Crawford, N3MBH - info(at)openrepeater(dot)com
# Licended under GPL v2 or later

/*
This script reads settings from the OpenRepeater database and builds new configuration
files for SVXLink.
*/

/* ---------------------------------------------------------- */
/* SESSION CHECK TO SEE IF USER IS LOGGED IN. */
session_start();
if ((!isset($_SESSION['username'])) || (!isset($_SESSION['userID']))){
	header('location: login.php');
} else { // If they are, show the page.
	/* ---------------------------------------------------------- */

	// Declare Config Arrays
	$config_array = [];
	$config_array['GLOBAL'] = []; // Declare empty for prioritization

	/* ---------------------------------------------------------- */
	/* --- LOAD CLASSES --- */

	# AUTOLOAD CLASSES
	require_once(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/includes/autoloadClasses.php');

	// Access database for Settings, Modules, Ports, and GPIOs
	$classDB = new Database();
	$settings = $classDB->get_settings();
	$module = $classDB->get_modules();
	$ports = $classDB->get_ports();
	$gpio = $classDB->get_gpios();
	$macros = $classDB->get_macros();

	$classSVXLink = new SVXLink($settings, $ports, $module);
	$classSVXLinkTCL = new SVXLink_TCL($settings);
	$classSVXLinkGPIO = new SVXLink_GPIO($gpio);

	/* ---------------------------------------------------------- */
	/* --- LOGIC SETTINGS --- */

	$classSVXLink->delete_custom_evnets(); // Purge Previous Custom Event Files

	foreach ($ports as $key => $val) {
		if ( $val['portEnabled'] == 1 ) {
			###############################################
			# Work around until new UI is in place
			###############################################
			if ( !isset($val['portDuplex']) ) {
				switch ($settings['orp_Mode']) {
				case "repeater":
					if ($key == 1) { $val['portDuplex'] = 'full'; } else { $val['portDuplex'] = 'half'; }
					$val['linkGroup'] = 1;
					break;
				case "simplex":
					$val['portDuplex'] = 'half'; // or full or half
					$val['linkGroup'] = 1;
					break;
				}
			}
			###############################################


			// Build Ports
			$config_array += $classSVXLink->build_rx( $key, $val['portType'] ); // Build RX
			$config_array += $classSVXLink->build_tx( $key, $val['portType'] ); // Build TX

			if ($val['portDuplex'] == 'full') {
				// Full Duplex Logic
				$config_array += $classSVXLink->build_full_duplex_logic($key);

				$new_logic_name = $classSVXLink->logicFullPrefix . $key;
				$new_logic_filename = $new_logic_name . '.tcl';
				$new_event = $classSVXLinkTCL->alias_RepeaterLogic($new_logic_name);

				$new_event = $classSVXLinkTCL->override_courtesy_tone($new_event);

			} else {
				// Half Duplex Logic
				$config_array += $classSVXLink->build_half_duplex_logic($key);

				$new_logic_name = $classSVXLink->logicHalfPrefix . $key;
				$new_logic_filename = $new_logic_name . '.tcl';
				$new_event = $classSVXLinkTCL->alias_SimplexLogic($new_logic_name);
			}
			$classSVXLink->write_config($new_event, $new_logic_filename, 'text');

			// Add to LinkGroup
			if (isset($val['linkGroup'])) {
				$linkGroupArray[$val['linkGroup']][$key] = $new_logic_name;
			}

		}
	}


	// BUILD LINK SECTION - If link group contains 2 or more ports...built config
	if (isset($linkGroupArray)) {
		foreach ($linkGroupArray as $grpNumber => $grpArray) {
			if (count($grpArray) > 1) {
				$config_array += $classSVXLink->build_link( $grpNumber, $grpArray );
			}
		}
	}


	// BUILD MACRO SECTION
	$config_array += $classSVXLink->build_macro($macros);
	if (isset($classSVXLink->macros)) {
		foreach($classSVXLink->macros as $curLogicSect => $curMacroSect) {
			$config_array[$curLogicSect]['MACROS'] = $curMacroSect;
		}
	}


	// GLOBAL SETTINGS
	$config_array['GLOBAL'] += $classSVXLink->build_global();

	// LOCATION SETTINGS
	$config_array += $classSVXLink->build_location();
	$config_array['GLOBAL']['LOCATION_INFO'] = $classSVXLink->location;

	// Build GPIO Config
	$gpioConfigFile = $classSVXLinkGPIO->build_gpio_config();

	// Insert Logic TCL Overrides
	$logicOverride = $classSVXLinkTCL->logic_override();

	// WRITE CONFIGURATION & TCL FILES
	$classSVXLink->write_config($config_array, 'svxlink.conf', 'ini');
	$classSVXLink->write_config($logicOverride, 'Logic.tcl', 'text');
	$classSVXLink->write_config($gpioConfigFile, 'gpio.conf', 'text');




	/* ---------------------------------------------------------- */
	/* FINISH UP */

	/* CLEAR SETTINGS UPDATE FLAG TO CLEAR BANNER AT TOP OF PAGE */
	$classDB->set_update_flag(false);

	########
	# Redundant GPIO teardown/setup to work around SVXLink issue
	$shellout = shell_exec('sudo /usr/sbin/orp_helper svxlink gpio_down');
	$shellout = shell_exec('sudo /usr/sbin/orp_helper svxlink gpio_up');
	########

	$shellout = shell_exec('sudo /usr/sbin/orp_helper svxlink restart');

	/* WHAT PAGE TO GO BACK TO */
	if (isset($_POST["return_url"])) {
		// Return to page that sent here
		$url = strtok($_POST["return_url"], '?'); //Clean parameters from URL
		header('location: '.$url);
	} else if (isset($_SESSION["new_repeater_settings"])) {
			// Wizard was run. Go ahead and destroy session and logout
			session_destroy();
			header('location: ../login.php');
		} else {
		// Otherwise just go to dashboard
		header('location: ../dashboard.php');
	}
?>

<?php
	/* ---------------------------------------------------------- */
	// SESSION CHECK TO SEE IF USER IS LOGGED IN.
} // close ELSE to end login check from top of page
/* ---------------------------------------------------------- */
?>