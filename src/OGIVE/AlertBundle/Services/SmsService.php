<?php

namespace OGIVE\AlertBundle\Services;
use Twilio\Rest\Client;
/**
 * Description of MailService
 *
 * @author Eric TONYE
 */
class SmsService {

    protected $twilio_client;
    protected  $username = "ACdda206efadd5de15c5f0121a6a982a9c";
    protected  $password = "e77b1a26567145aa789a3dc0d701dea0";

    public function __construct() {
        $this->twilio_client = new Client($this->username, $this->password);
    }

    public function sendSms($phoneNumber, $message) {
        if ($phoneNumber != "") {
            $this->twilio_client->messages->create(
                    $phoneNumber, // Text any number
                    array(
                'from' => 'OGIVE INFOS', // From a Twilio number in your account
                'body' => $message
                    )
            );
        } else {
            return true;
        }
    }

}