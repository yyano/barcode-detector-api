<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use \vendor\symfony\process\ProcessBuilder;
use Symfony\Component\Process\ProcessBuilder;

class Barcode extends Model {

    /**
     * Upload max filesize.
     */
    //const FILE_MAXSIZE = 1048576;   // 1MB
    const FILE_MAXSIZE = 2097152;   // 2MB

    /**
     * Detector barcode rotate step.
     */
    const ROTATE_STEP = 15;

    /**
     * Check filetype of image.
     * @param type $filename
     * @return boolean
     */
    function isImage( $filename ) {

        if( filesize( $filename ) > 11 ) {
            if( false !== exif_imagetype( $filename ) ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * Check filesize.
     * @param type $filesize
     * @return boolean
     */
    function isSize( $filesize ) {
        if( self::FILE_MAXSIZE >= $filesize ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * detect barcode from image.
     * @param type $imegefile
     * @return boolean
     */
    function detectImage( $imegefile ) {
        $builder = new ProcessBuilder();
        $builder->setPrefix( getenv( 'ZBARIMG_PATH' ) . '/zbarimg' );
        $builder->setArguments( ['-q', $imegefile ] )->enableOutput();

        try {
            $process = $builder->getProcess();
            //$process->mustRun();
            $process->run();
            $results = $process->getOutput();
        } catch( ProcessFailedException $e ) {
            //echo $e->getTraceAsString();
            return false;
        }

        $value = explode( ":", trim( $results ) );

        if( count( $value ) > 1 ) {
            $barcode['type']  = $value[0];
            $barcode['value'] = $value[1];
            return $barcode;
        } else {
            return false;
        }
    }

    /**
     * Barcode detector / image resize and rotate
     * @param type $imegefile
     * @return type
     */
    function detectorBarcode( $imegefile ) {
        $image = new \Imagick( $imegefile );

        //resize
        if( 1000 < $image->getImageWidth() ) {
            $image->scaleImage( floor( $image->getImageWidth() / 2 ), 0 );
        }

        if( 1000 < $image->getImageHeight() ) {
            $image->scaleImage( 0, floor( $image->getImageHeight() / 2 ) );
        }

        $filename = $this->setFilename( $imegefile, 0 );
        $image->writeImage( $filename );

        unset( $result );
        $result = $this->detectImage( $filename );
        if( is_array( $result ) ) {
            return $result;
        }

        // rotate image
        for( $deg = 0; $deg < 180; $deg = $deg + self::ROTATE_STEP ) {
            $image = new \Imagick( $filename );

            $filerotate = $this->setFilename( $imegefile,
                    $deg + self::ROTATE_STEP );
            $image->rotateImage( new \ImagickPixel( '#00000000' ),
                    $deg + self::ROTATE_STEP );
            $image->writeImage( $filerotate );

            unset( $result );
            $result = $this->detectImage( $filerotate );
            if( is_array( $result ) ) {
                return $result;
            }
        }
    }

    /**
     * Set image file name.
     * @param type $Filename
     * @param type $roteta
     * @return string
     */
    function setFilename( $Filename, $roteta ) {

        $pathinfo = pathinfo( $Filename );
        //var_dump($pathinfo);

        $name = $pathinfo['dirname']
                . '/'
                . $pathinfo['filename']
                . '_'
                . sprintf( '%03d', $roteta )
                . '.'
                . $pathinfo['extension'];
        return $name;
    }

}
