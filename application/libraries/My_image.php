<?php


class My_image {

	// function to strip 
	public function stripEXIF($array,$destination){
		// array['upload_data']['full_path']
		$results = array('destination' => '',
				 'file_name' => '',
				 'format' => '' );

		if (extension_loaded('magickwand') && function_exists("NewMagickWand")) {
			/* ImageMagick is installed and working */
			$img = new Imagick($array['full_path']);
			$img->stripImage();
			$img->setImageFormat('png');
			$img->writeImage($destination);
			$img->clear();

			$results['file_name'] = $array['raw_name'].'.png';
			$results['destination'] = $destination;
			$results['format'] = '.png';
		} elseif (extension_loaded('gd') && function_exists('gd_info')) {
			/* GD is installed and working */
			if($array['file_ext'] == '.png'){
				$img = imagecreatefrompng($array['full_path']);
			} elseif($array['file_ext'] == '.jpeg' || $array['file_ext'] == '.jpg'){
				$img = imagecreatefromjpeg($array['full_path']);
			} elseif($array['file_ext'] == '.gif' ){
				$img = imagecreatefromgif($array['full_path']);
			}			
			
			imagepng($img, $destination);
			$results['file_name'] = $array['raw_name'].'.png';
			$results['destination'] = $destination;
			$results['format'] = '.png';
		} else {
			// Neither PHP-GD or ImageMagick is installed, no EXIF filtering.
			$results['file_name'] = $array['file_name'];
			$results['destination'] = $array['full_path'];
			$results['format'] = $array['file_ext'];
		}
		return $results;
	}

	public function displayImage($imageHash,$height = NULL, $width = NULL){	
		// To get an image, call this function.
		$CI = &get_instance();
		$CI->load->model('images_model');
		
		// Check if the image is already held in the database
		// If the DB is missing the entry, BitWasp will try to find the file, and then encode it and add to the DB.
		$image = $CI->images_model->imageFromDB($imageHash);

        	if($image === FALSE) {
			// Image identifier is invalid.
			return FALSE;
		} else {
			// Return the <img> tag with base64 encoded image, and the height
			if($height !== NULL){ $displayHeight = $height; } else { $displayHeight = $image['height']; }
			if($width !== NULL){ $displayWidth = $width; } else { $displayWidth = $image['width']; }

			$result = array('imageHash' => $imageHash,
					'encoded' => $image['encoded'],
					'height'  => $displayHeight,
					'width'   => $displayWidth );
			return $result;
		}
	}

	// This function is displays an image without adding to the DB. Useful for catpchas.
	public function displayTempImage($identifier){
		$CI = &get_instance();
	
		$image = $this->simpleImageEncode($identifier);
		$validHTML = "<img src=\"data:image/gif;base64,{$image}\" />";
		return $validHTML;
	}


	// This function returns the base64 string from an image.
	public function simpleImageEncode($identifier){
		$imageFile = file_get_contents('./assets/images/'.$identifier);
		// Encode to base64/
		$validImage = base64_encode($imageFile);
	//	$validImage = chunk_split($validImage, 64, "\n");			// Uncomment this for issues with new lines

		return $validImage;
	}



};

