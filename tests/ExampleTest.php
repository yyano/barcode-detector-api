<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;

class ExampleTest extends TestCase {

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample() {
        $this->get( '/' );

        $this->assertEquals(
                $this->response->getContent(), $this->app->version()
        );
    }

    public function setUp() {
        parent::setUp();

        // fail imagefile 
        $filepath = base_path( 'tests/fixtures' ) . '/' . 'fail_barcode.jpg';
        copy( base_path( 'tests/fixtures' ) . '/' . 'fail_barcode.orig.jpg',
                $filepath );
        
        // pass imagefile 
        $filepath = base_path( 'tests/fixtures' ) . '/' . 'pass_barcode.jpg';
        copy( base_path( 'tests/fixtures' ) . '/' . 'pass_barcode.orig.jpg',
                $filepath );
    }
    
    /**
     * API Path
     */
    const API_URLPATH = '/v1/detect';

    public function testPost() {
        /**
         *  no file
         */
        $this->post( self::API_URLPATH )
                ->seeJsonEquals( ['result' => false, 'message' => 'File not found.' ] );

        /**
         * file upload is failed.
         */
        $filepath = base_path( 'tests/fixtures' ) . '/' . 'fail_text.txt';

        $upfile_text = new UploadedFile(
                $filepath, 'fail_text.txt', 'text/plane', filesize( $filepath ),
                null, false   // upload failed.
        );

        $response = $this->call( 'POST', self::API_URLPATH, [ ], [ ],
                ['file' => $upfile_text ] );

        $this->assertJsonStringEqualsJsonString(
                $response->content(),
                json_encode( [
            'result'  => false,
            'message' => "File valid error.",
        ] ) );

        /**
         * upload file is not imagefile.
         */
        $filepath = base_path( 'tests/fixtures' ) . '/' . 'fail_text.txt';

        $upfile_text = new UploadedFile(
                $filepath, 'fail_text.txt', 'text/plane', filesize( $filepath ),
                null, true
        );

        $response = $this->call( 'POST', self::API_URLPATH, [ ], [ ],
                ['file' => $upfile_text ] );

        $this->assertJsonStringEqualsJsonString(
                $response->content(),
                json_encode( [
            'result'  => false,
            'message' => "File type is not image.",
        ] ) );

        /**
         * upload file is very large.
         */
        $filepath = base_path( 'tests/fixtures' ) . '/' . 'fail_large.jpg';

        $upfile_large = new UploadedFile(
                $filepath, 'fail_large.jpg', 'image/jpeg',
                filesize( $filepath ), null, true
        );

        $response = $this->call( 'POST', self::API_URLPATH, [ ], [ ],
                ['file' => $upfile_large ] );

        $this->assertJsonStringEqualsJsonString(
                $response->content(),
                json_encode( [
            'result'  => false,
            'message' => "File size is too large.",
        ] ) );

        /**
         * upload file is OK. not found barcode.
         */
        $filepath = base_path( 'tests/fixtures' ) . '/' . 'fail_barcode.jpg';

        $upfile_no_barcode = new UploadedFile(
                $filepath, 'fail_barcode.jpg', 'image/jpeg',
                filesize( $filepath ), null, true
        );

        $response = $this->call( 'POST', self::API_URLPATH, [ ], [ ],
                ['file' => $upfile_no_barcode ] );

        $this->assertJsonStringEqualsJsonString(
                $response->content(),
                json_encode( [
            'result'  => false,
            'message' => "Barcode not found.",
        ] ) );

        /**
         * upload file is OK. find barcode.
         */
        $filepath = base_path( 'tests/fixtures' ) . '/' . 'pass_barcode.jpg';

        $upfile_pass_image = new UploadedFile(
                $filepath, 'pass_barcode.jpg', 'image/jpeg',
                filesize( $filepath ), null, true
        );

        $response = $this->call( 'POST', self::API_URLPATH, [ ], [ ],
                ['file' => $upfile_pass_image ] );

        $this->assertJsonStringEqualsJsonString(
                $response->content(),
                json_encode( [
            'result'  => true,
            'message' => "Barcode find.",
            'barcode' => [
                'type'  => 'EAN-13',
                'value' => '4901277227410'
            ]
        ] ) );
    }

}
