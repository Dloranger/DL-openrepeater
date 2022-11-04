<?php
#####################################################################################################
# Functions Class
#####################################################################################################

class Functions {

	public $configFileArray = []; // Array of written config files
	private $orpFileHeader;
	

	public function __construct() {
		$orpFileHeader = '
		###############################################################################
		#
		#  OPENREPEATER / SVXLINK CONFIGURATION FILE
		#  This file was auto generated by OpenRepeater.
		#  DO NOT MAKE CHANGES IN THIS FILE AS THEY WILL BE OVERWRITTEN
		#
		###############################################################################';
		#Clean up tabs/white spaces
		$this->orpFileHeader = trim(preg_replace('/\t+/', '', $orpFileHeader)) . "\n\n";
	}



	###############################################
	# Build INI Format
	###############################################

	public function build_ini($input_array) {
		$section_separator = '###############################################################################';

		$ini_return = "";
		$section_count = 0;
		foreach($input_array as $ini_section => $ini_section_array) {
			$section_count++;
			if ($section_count > 1) { $ini_return .= $section_separator . "\n\n";}
			$ini_return .= "[" . $ini_section . "]\n";
			foreach($ini_section_array as $key => $value) {
				$value = trim($value);
				if ($value != '') {
					$ini_return .= $key . "=" . $value . "\n";
				}
			}
			$ini_return .= "\n";
		}

		return $ini_return;
	}



	###############################################
	# Write Config File
	###############################################

	public function write_config($data, $filename, $format = 'text') {

		if ($format == 'ini') {
			$data = $this->build_ini($data); // Convert Array to INI format
		}
		$file_output = $this->orpFileHeader . $data;

		switch ($filename) {
		case "svxlink.conf":
			$filepath = '/etc/svxlink/';
			break;
		case "gpio.conf":
			$filepath = '/etc/svxlink/';
			break;
		case "devices.conf":
			$filepath = '/etc/svxlink/';
			break;
		case "Logic.tcl":
			$filepath = '/usr/share/svxlink/events.d/local/';
			break;
		case (strpos($filename, "Module") === 0): // Begins with Module
			$filepath = '/etc/svxlink/svxlink.d/';
			break;
		case (strpos($filename, "ORP_") === 0): // Event beginning with ORP_
			$filepath = '/usr/share/svxlink/events.d/';
			break;
		}

		$full_file_path = $filepath . $filename;

		// If Directory Doesn't Exist, create it.
		if (!is_dir($filepath)) { mkdir($filepath); }

		// Write the File.
		file_put_contents( $full_file_path, $file_output );
		
		// Save this config information to array
		$this->configFileArray[] = [ 'fileLabel' => $filename, 'filePath' => $full_file_path ];
		
		}


	// Write Active Config Files & Modules to DB for later reference. 
	public function save_config_list($inputArray) {
		$inputArray = array_unique($inputArray, SORT_REGULAR); // Removed duplicates
		$configFileArray = json_encode($inputArray);
		$Database = new Database();
		$sql = "UPDATE system_flags SET value='$configFileArray' WHERE keyID='config_files'";
		$Database->update($sql);
	}



	###############################################
	# Internet Functions
	###############################################

	public function internet_connection() {
	    $connected = @fsockopen("google.com", 443); 
		if ($connected){
			fclose($connected);
			return true; // Has internet connection
		} else {
			return false; // No internet connection
		}
		return $is_conn;	
	}


	public function get_public_ip() {
		$publicIP = file_get_contents("https://ipecho.net/plain"); // returns public IP only
		return $publicIP;
	}



	###############################################
	# Geo Functions
	###############################################

	public function get_geo_location() {
		
		### THIS FUNCTION IS A WORK IN PROGRESS ###

		# Attempt to get location from GPS
		$System = new System();
		$gps_result = json_decode( $System->orp_helper_call('gps','read'), true );
		
		# Try GPS location first...
		if ( isset($gps_result['lat']) && isset($gps_result['lon']) ) {
			$geo_results = $gps_result;
			$geo_results['status'] = 'gps_geo';

		} else {
			# If cannot get GPS location, then try locaiton of public IP address
			if ($this->internet_connection() == true) {
				$ip_loc_details = $this->get_ip_location();
		
				$geo_results['status'] = 'ip_geo';
				$geo_results['lat'] = $ip_loc_details['lat']; // Latitude
				$geo_results['lon'] = $ip_loc_details['lon']; // Longitude
				$geo_results['ip'] = $ip_loc_details['ip']; // External IP

			# No internet connection or GPS location
			} else {
				$geo_results['status'] = 'nofix';
			}

		}

		return $geo_results;
		
	}
	

	public function get_ip_location() {
		$public_ip = $this->get_public_ip();
		$details = json_decode(file_get_contents("http://ipinfo.io/{$public_ip}/json"),true);

		// Split location and add to array as separate latitude & longitude
		$location_array = explode(',', $details['loc']);
		$details['lat'] = trim($location_array[0]); // Latitude
		$details['lon'] = trim($location_array[1]); // Longitude

		return $details;
	}
	
		
	public function geo_convert($latitude, $longitude, $format=null) {
		$latitudeDirection = $latitude < 0 ? 'S': 'N';
		$longitudeDirection = $longitude < 0 ? 'W': 'E';

		$latitudeNotation = $latitude < 0 ? '-': '';
		$longitudeNotation = $longitude < 0 ? '-': '';

		$latitudeInDegrees = floor(abs($latitude));
		$longitudeInDegrees = floor(abs($longitude));

		$latitudeDecimal = abs($latitude)-$latitudeInDegrees;
		$longitudeDecimal = abs($longitude)-$longitudeInDegrees;

		$latParts = explode(".",$latitude);
		$latTempma = "0.".$latParts[1];
		$latTempma = $latTempma * 3600;
		$latitudeMinutes = floor($latTempma / 60);
		$latitudeSeconds = $latTempma - ($latitudeMinutes*60);

		$longParts = explode(".",$longitude);
		$longTempma = "0.".$longParts[1];
		$longTempma = $longTempma * 3600;
		$longitudeMinutes = floor($longTempma / 60);
		$longitudeSeconds = $longTempma - ($longitudeMinutes*60);

		switch ($format) {
		case 'svxlink':
			$precision = 0;
			$latitudeSeconds = round($latitudeSeconds,$precision);
			$latitudeSeconds = $latitudeSeconds > 59 ? 59 : $latitudeSeconds; // Rounding Tweak
			$longitudeSeconds = round($longitudeSeconds,$precision);
			$longitudeSeconds = $longitudeSeconds > 59 ? 59 : $longitudeSeconds; // Rounding Tweak
			$outputFormat = '%s.%s.%s%s'; // SVXLink Format
			break;
		default:
			$precision = 1;
			$latitudeSeconds = round($latitudeSeconds,$precision);
			$longitudeSeconds = round($longitudeSeconds,$precision);
			$outputFormat = '%s°%s\'%s"%s'; // Google DMS
		}

		return [
		'latitude' => sprintf($outputFormat,$latitudeInDegrees,$latitudeMinutes,$latitudeSeconds,$latitudeDirection),
		'longitude' => sprintf($outputFormat,$longitudeInDegrees,$longitudeMinutes,$longitudeSeconds,$longitudeDirection)
		];
	}


}