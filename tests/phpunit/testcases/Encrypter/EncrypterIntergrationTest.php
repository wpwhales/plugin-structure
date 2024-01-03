<?php

namespace Tests\Encrypter;



use WPWhales\Contracts\Encryption\DecryptException;

class EncrypterIntergrationTest extends \WP_UnitTestCase
{




    public function test_encrypt_decrypt_data(){

        $encrypter = $this->app["encrypter"];
        $text = "SOMETHING";
        $encrypted = $encrypter->encrypt($text);


        $this->assertEquals($encrypter->decrypt($encrypted),"SOMETHING");
    }


    public function test_decrypt_failed_data(){

        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage("The payload is invalid.");
        $encrypter = $this->app["encrypter"];

        $encrypter->decrypt(123);
    }





}

