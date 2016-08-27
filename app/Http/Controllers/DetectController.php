<?php

namespace App\Http\Controllers;

use App\Article;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DetectController extends Controller {

    public function postDetect( Request $request ) {

        $errorMessage = null;

        //get uploaded file infomation.
        if( $request->hasFile( 'file' ) ) {
            $tmpFile = $request->file( 'file' );
        } else {
            $errorMessage = 'File not found.';
        }

        // file upload failed.
        if( !isset( $errorMessage ) ) {
            if( !$tmpFile->isValid() ) {
                $errorMessage = 'File valid error.';
            }
        }

        $barcode = new \App\Barcode();

        //check filetype
        if( !isset( $errorMessage ) ) {
            if( !$barcode->isImage( $tmpFile->getPathname() ) ) {
                $errorMessage = 'File type is not image.';
            }
        }

        //check file size
        if( !isset( $errorMessage ) ) {
            if( !$barcode->isSize( $tmpFile->getSize() ) ) {
                $errorMessage = 'File size is too large.';
            }
        }

        if( !isset( $errorMessage ) ) {
            //move file
            $filename = date( 'His' ) . '.' . $tmpFile->getClientOriginalExtension();

            $filedir = storage_path( 'upload' ) . '/' . date( 'Ymd' );
            if( !file_exists( $filedir ) ) {
                mkdir( $filedir );
            }

            try {
                $objFile = $tmpFile->move( $filedir, $filename );
            } catch( Exception $exc ) {
                echo $exc->getTraceAsString();
            }

            $result = $barcode->detectorBarcode( $objFile->getPathname() );

            if( $result ) {
                return response()->json( [
                    'result' => true, 
                    'message' => 'Barcode find.',
                    'barcode' => ['type' => $result['type'],
                                'value' => $result['value'] ] ] );
            } else {
                return response()->json( ['result' => false, 'message' => 'Barcode not found.' ] );
            }
        } else {
            return response()->json( ['result' => false, 'message' => $errorMessage ] );
        }
    }

}
