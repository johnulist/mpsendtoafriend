<?php
/**
 * This is a PHP library that handles calling reCAPTCHA.
 *
 * @link      http://www.google.com/recaptcha
 * @author    Google, Inc.
 * @copyright Copyright (c) 2014, Google Inc.
 * @license   public domain
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Documentation and latest version
 * https://developers.google.com/recaptcha/docs/php
 * Get a reCAPTCHA API Key
 * https://www.google.com/recaptcha/admin/create
 * Discussion group
 * http://groups.google.com/group/recaptcha
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__).'/recaptcharesponse.php');

class RecaptchaLib
{
    private static $signup_url = 'https://www.google.com/recaptcha/admin';
    private static $site_verify_url =
        'https://www.google.com/recaptcha/api/siteverify?';
    private $secret;
    private static $version = 'php_1.0';

    /**
     * Constructor.
     *
     * @param string $secret shared secret between site and ReCAPTCHA server.
     */
    public function __construct($secret)
    {
        if ($secret == null || $secret == '') {
            die("To use reCAPTCHA you must get an API key from <a href='"
                .self::$signup_url."'>".self::$signup_url.'</a>');
        }
        $this->secret = $secret;
    }

    /**
     * Encodes the given data into a query string format.
     *
     * @param array $data array of string elements to be encoded.
     *
     * @return string - encoded request.
     */
    private function encodeQs($data)
    {
        $req = '';
        foreach ($data as $key => $value) {
            $req .= $key.'='.urlencode(Tools::stripslashes($value)).'&';
        }

        // Cut the last '&'
        $req = Tools::substr($req, 0, Tools::strlen($req) - 1);

        return $req;
    }

    /**
     * Submits an HTTP GET to a reCAPTCHA server.
     *
     * @param string $path url path to recaptcha server.
     * @param array $data array of parameters to be sent.
     *
     * @return array response
     */
    private function submitHttpGet($path, $data)
    {
        $req = $this->encodeQs($data);
        $response = Tools::file_get_contents($path.$req);

        return $response;
    }

    /**
     * Calls the reCAPTCHA siteverify API to verify whether the user passes
     * CAPTCHA test.
     *
     * @param string $remote_ip IP address of end user.
     * @param string $response response string from recaptcha verification.
     *
     * @return RecaptchaResponse
     */
    public function verifyResponse($remote_ip, $response)
    {
        // Discard empty solution submissions
        if ($response == null || Tools::strlen($response) == 0) {
            $recaptcha_response = new RecaptchaResponse();
            $recaptcha_response->success = false;
            $recaptcha_response->error_codes[] = 'missing-input';

            return $recaptcha_response;
        }

        $get_response = $this->submitHttpGet(
            self::$site_verify_url,
            array(
                'secret' => $this->secret,
                'remoteip' => $remote_ip,
                'v' => self::$version,
                'response' => $response
            )
        );
        if (empty($get_response)) {
            $error = error_get_last();
            Logger::addLog(sprintf('reCAPTCHA: Could not contact Google. Check your server settings. Error: %s', $error['message']), 3);

            if (!Configuration::get('MPRECAPTCHA_GOOGLEIGNORE')) {
                $recaptcha_response = new RecaptchaResponse();
                $recaptcha_response->success = false;
                $recaptcha_response->error_codes[] = 'google-no-contact';

                return $recaptcha_response;
            } else {
                $recaptcha_response = new RecaptchaResponse();
                $recaptcha_response->success = true;

                return $recaptcha_response;
            }
        }
        $answers = Tools::jsonDecode($get_response, true);
        $recaptcha_response = new RecaptchaResponse();

        if (trim($answers['success']) == true) {
            $recaptcha_response->success = true;
        } else {
            $recaptcha_response->success = false;
            @$recaptcha_response->error_codes = $answers['error-codes'];
        }

        return $recaptcha_response;
    }
}
