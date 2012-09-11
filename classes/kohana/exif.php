<?php

defined('SYSPATH') or die('No direct access allowed.');

require_once(Kohana::find_file('vendor', 'exif/exif'));

/**
 * EXIF class
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Kohana_EXIF {

    /**
     * Parsed exif data
     *
     * @var  array
     */
    public $exif = array();

    /**
     * Unparsed exif data
     *
     * @var  array
     */
    public $exif_raw = array();

    /**
     * Parse only these variables
     *
     * @var  array
     */
    public $exif_vars = array(
        'make'          => array('IFD0','Make'),
        'model'         => array('IFD0', 'Model'), // Camera model
        'lens'          => array('SubIFD','MakerNote','LensInfo'), // Lens information
        'software'      => array('IFD0','Software'), // Lens information
        'ccdwidth'      => array('SubIFD', 'CCDWidth'),
        'exposure'      => array('SubIFD', 'ExposureTime'), // Shutter speed
        'aperture'      => array('SubIFD', 'FNumber'), // Aperture
        'focal'         => array('SubIFD', 'FocalLength'), // Focal length
        'iso'           => array('SubIFD', 'ISOSpeedRatings'), // ISO sensitivity
        'taken'         => array('SubIFD', 'DateTimeOriginal'), // Time taken
        'flash'         => array('SubIFD', 'Flash'), // Flash fired
        'latitude'      => array('GPS', 'Latitude'), // Latitude
        'longitude'     => array('GPS', 'Longitude'), // Longitude
        'altitude'      => array('GPS', 'Altitude'), // Altitude
        'altitude_ref'  => array('GPS', 'Altitude Reference'), // Altitude reference
    );
    public $filename;

    public function __construct($filename) {
        if (!empty($filename)) {
            if (!is_file($filename)) {
                throw new Kohana_Exception('Image not found: :file', array(':file' => $filename));
            }
            if (!is_readable($filename)) {
                throw new Kohana_Exception('Image not readable: :file', array(':file' => $filename));
            }
            $this->filename = $filename;
        }
    }

    public static function factory($filename) {
        return new self($filename);
    }

    public function read() {
        $exif = array();

        // Read raw EXIF data
        $exif_raw = read_exif_data_raw($this->filename, false);
        
        $this->exif_raw = $exif_raw;
        if (isset($exif_raw['ValidEXIFData'])) {
            foreach ($this->exif_vars as $field => $exif_var) {
                switch(count($exif_var)) {
                    case 1:
                        if (array_key_exists($exif_var[0], $exif_raw))
                            $exif[$field] = $exif_raw[$exif_var[0]];
                    break;
                    case 2:
                        if (array_key_exists($exif_var[0], $exif_raw)
                            && array_key_exists($exif_var[1], $exif_raw[$exif_var[0]]))
                            $exif[$field] = $exif_raw[$exif_var[0]][$exif_var[1]];
                    break;
                    case 3:
                        if (array_key_exists($exif_var[0], $exif_raw)
                            && array_key_exists($exif_var[1], $exif_raw[$exif_var[0]])
                            && array_key_exists($exif_var[2], $exif_raw[$exif_var[0]][$exif_var[1]]))
                            $exif[$field] = $exif_raw[$exif_var[0]][$exif_var[1]][$exif_var[2]];
                    break;
                }
            }
        }

        $this->exif = $exif;
        return $exif;
    }
    
    public function getRaw() {
        return $this->exif_raw;
    }

}