<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Session;
use Storage;
use View;
use Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use App\Common;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Mail\MyMail;
use Illuminate\Support\Facades\Mail;
use App\Models\{
    User
};

class CCAvenueGateway extends Controller
{

    public function CCencrypt($plainText, $key)
    {
        $key = $this->hextobin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }

    public function CCdecrypt($encryptedText, $key)
    {
        $key = $this->hextobin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = $this->hextobin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }
    //*********** Padding Function *********************

    public function pkcs5_pad($plainText, $blockSize)
    {
        $pad = $blockSize - (strlen($plainText) % $blockSize);
        return $plainText . str_repeat(chr($pad), $pad);
    }

    //********** Hexadecimal to Binary function for php 4.0 version ********

    public function hextobin($hexString)
    {
        $length = strlen($hexString);
        $binString = "";
        $count = 0;
        while ($count < $length) {
            $subString = substr($hexString, $count, 2);
            $packedString = pack("H*", $subString);
            if ($count == 0) {
                $binString = $packedString;
            } else {
                $binString .= $packedString;
            }

            $count += 2;
        }
        return $binString;
    }

    public function invokeApiRequest($type, $url, $headers, $post)
    {
        try {
            $curl = curl_init();

            curl_setopt_array(
                $curl,
                array(

                    CURLOPT_URL => $url,

                    CURLOPT_RETURNTRANSFER => true,

                    CURLOPT_ENCODING => '',

                    CURLOPT_MAXREDIRS => 10,

                    CURLOPT_TIMEOUT => 0,

                    CURLOPT_FOLLOWLOCATION => true,

                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

                    CURLOPT_CUSTOMREQUEST => $type,

                    CURLOPT_POSTFIELDS => $post,

                    CURLOPT_HTTPHEADER => $headers,

                )
            );

            $response = curl_exec($curl);

            curl_close($curl);

            return json_decode($response);
        } catch (Exception $e) {

            $response = ['status' => 'failed', 'message' => 'Throw in Catch Section', 'error' => ['message' => $e->getMessage(), 'code' => $e->getCode(), 'string' => $e->__toString()]];
            return response()->json($response);
        }
    }
    function run_Api($method, $url)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
            )
        );

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function StoreThePaymentDetails(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'pay_categories' => 'required',
            'amount' => 'required',
            'billing_name' => 'required|string',
            'billing_email' => 'required|email',
            'billing_tel' => 'required|string|regex:/^[6-9]\d{9}$/',
            'billing_address1' => 'required|string',
            'billing_address2' => 'required|string',
            'billing_state' => 'required|string',
            'billing_city' => 'required|string',
            'billing_zip' => 'required|string',
            'billing_country' => 'required|string',
            'finaltotal' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 401, 'message' => 'Validation Failed', 'errors' => $validator->errors()]);
        }
        $totalAmount = array_sum($request->amount);
        if ($totalAmount != $request->finaltotal) {
            return response()->json(['status' => 400, 'message' => 'Final total amount is invalid']);
        }
        $ses_user = Session::get('user');
        $trans_id = Str::random(16);
        $ins_arr = [
            'user_id' => $ses_user[0]->ID,
            'order_id' => $trans_id,
            'pay_categories' => json_encode($request->pay_categories),
            'amount' => json_encode($request->amount),
            'language' => 'EN',
            'billing_name' => $request->billing_name,
            'billing_address1' => $request->billing_address1,
            'billing_address2' => $request->billing_address2,
            'billing_city' => $request->billing_city,
            'billing_state' => $request->billing_state,
            'billing_zip' => $request->billing_zip,
            'billing_country' => $request->billing_country,
            'billing_tel' => $request->billing_tel,
            'billing_email' => $request->billing_email,
            'tid' => $trans_id,
            'nenc_request' => '',
            'pay_request' => '',
            'payment_type' => '',
            'gateway' => '',
            'reference' => '',
            'redirect_url' => '',
            'finaltotal' => (float) $request->finaltotal,
            'pay_status' => 'pending',
            'pay_re_status' => '',
            'enc_response' => '',
            'pay_response' => '',
            'pay_type' => $request->pay_type,
            'calendar_type' => $request->calendar_type ?? '',
            'created_at' => date("Y-m-d H:i:s")
        ];
        $insert = DB::table('payment_history')->insertGetId($ins_arr);
        $data = DB::table('payment_history')->where('id', '=', $insert)->first();

        // $keyToRemove = ['id', 'nenc_request', 'pay_request', 'payment_type', 'gateway', 'reference', 'redirect_url', 'pay_status', 'pay_re_status', 'enc_response', 'pay_response', 'created_at', 'updated_at', 'deleted_at'];

        // foreach ($keyToRemove as $key) {
        //     if (isset($data->$key)) {
        //         unset($data->$key);
        //     }
        // }

        return response()->json($insert == true ? ['status' => 200, 'message' => 'Payment details successfully added', 'data' => $data] : ['status' => 400, 'message' => 'Failed']);
    }
    // Get User Product Cart Details
    public function ccavenueInitiate(Request $request)
    {

        try {
            $response = [];
            $input = $request->all();
            $ses_user = Session::get('user');

            if ($request->transaction_id == '' || $request->transaction_id == null || $request->transaction_id == 'null') {
                $response = ['status' => 'failed', 'message' => 'Please use a valid transaction id!', 'error' => 'Please use a valid transaction id!'];
                goto returnFVI;
            }

            $transaction_id = $request->transaction_id;
            $data = [];
            $user_id = $ses_user[0]->ID;
            if ($user_id == '' || $user_id == null || $user_id == 'null') {
                $response = ['status' => 'failed', 'message' => 'Login Required!', 'error' => 'Kindly check the access token!'];
                goto returnFVI;
            }

            if ($transaction_id == '' || $transaction_id == null || $transaction_id == 'null') {
                $response = ['status' => 'failed', 'message' => 'Transaction ID Required!', 'error' => 'Kindly check the transaction ID!'];
                goto returnFVI;
            }

            $payment_history = DB::table('payment_history')->select('tid', 'id', 'finaltotal', 'pay_status')->where('user_id', $user_id)->where('tid', 'LIKE', $transaction_id)->orderBy('id', 'desc')->limit(1)->get();

            if ($payment_history->count() < 1) {
                $response = ['status' => 'failed', 'message' => 'The payment track missing!'];
                goto returnFVI;
            }

            if ($payment_history[0]->pay_status == NULL) {
                $response = ['status' => 'failed', 'message' => 'The current not valid to process..!'];
                goto returnFVI;
            }

            if ($payment_history[0]->finaltotal != 0 && $payment_history[0]->finaltotal > 0) {

                $pay_details = DB::table('payment_history')
                    ->select('payment_history.*', 'state.name AS state_name', 'city.name AS city_name')
                    ->leftJoin('state', 'state.id', '=', 'payment_history.billing_state')
                    ->leftJoin('city', 'city.id', '=', 'payment_history.billing_city')
                    ->where('payment_history.tid', 'LIKE', $transaction_id)
                    ->where('payment_history.user_id', '=', $user_id)->get();
                // dd($pay_details);

                $firstname = preg_replace('/[0-9\@\.,\;\""!@#$%^&*()<>:;"]+/', '', $pay_details[0]->billing_name);
                $emailid = ($pay_details[0]->billing_email != '') ? $pay_details[0]->billing_email : '';
                $mobile = preg_replace('/[A-Za-z\@\.,\;\""!@#$%^&*()<>:; "]+/', '', $pay_details[0]->billing_tel);
                $address = preg_replace('/[@.;\|""!@$%^&*<>:;"]+/', '', $pay_details[0]->billing_address1 . ',' . $pay_details[0]->billing_address2);
                $nationality = preg_replace('/[0-9\@\.,\;\""!@#$%^&*()<>:;"]+/', '', $pay_details[0]->billing_country);
                $state = preg_replace('/[0-9\@\.,\;\""!@#$%^&*()<>:;"]+/', '', $pay_details[0]->state_name);
                $city = preg_replace('/[0-9@.;\|""!@$%^&*<>:;"]+/', '', $pay_details[0]->city_name);
                $zip = $pay_details[0]->billing_zip;

                $saveCard = ($request->saveCard != 'Y' && $request->saveCard != '') ? 'N' : 'Y';

                $ccRequest = [
                    "merchant_id" => (int) env('merchantId'),
                    "order_id" => $transaction_id,
                    "amount" => (int) $payment_history[0]->finaltotal,
                    "language" => "EN",
                    "saveCard" => $saveCard,
                    "billing_name" => $firstname,
                    "billing_address" => ($address != '') ? $address : $nationality,
                    "billing_city" => $city,
                    "billing_state" => $state,
                    "billing_zip" => $zip,
                    "billing_country" => $nationality,
                    "billing_tel" => $mobile,
                    "billing_email" => $emailid,
                    "redirect_url" => env('Base_URL') . 'payment-final',
                    "merchant_param1" => $user_id,
                    "cancel_url" => env('Base_URL') . "payment-final",
                    "tid" => $transaction_id,
                    "merchant_param2" => '',
                    "merchant_param3" => ''
                ];


                $merchant_data = http_build_query($ccRequest, '&');
                $merchant_data .= "&currency=INR";
                if ($saveCard === 'Y') {
                    $merchant_data .= "&customer_identifier=" . $user_id;
                }

                $encrypted_data = $this->CCencrypt($merchant_data, env('workingKey'));
                // dd($encrypted_data);
                $pay_trans_array = [
                    'nenc_request' => $merchant_data,
                    'pay_request' => json_encode($ccRequest),
                    'gateway' => 'ccavenue',
                    'payment_type' => 'online',
                    'reference' => strval($encrypted_data),
                    'pay_status' => 'inprogress',
                    'redirect_url' => '',
                    'updated_at' => date("Y-m-d H:i:s")
                ];
                // dd($pay_trans_array);
                // \DB::enableQueryLog();
                $updateResult = DB::table('payment_history')->where('tid', $transaction_id)
                    ->where('pay_status', 'pending')
                    ->where('id', $payment_history[0]->id)
                    ->update($pay_trans_array);
                // $enctdata = '652ba4c917355aac2176ae69555b26709f27e3189b0c347a291ba1908023fc819ec1beb00b98f36af868f86c81eeafbfa7ce5cfc3e1e6987624a12c35a32f854f67aae354c23255ff730a19e4b9663ae84fca5f70f58b6d794f41f67ddf6059e4226fcf4c75a9bca979e3d975a53e04a68392017c45ecce6923c8abfb14a7f457570896d01767e8b85c4ed773746972cadc2036c208b5ab4bffc34a17a1243a1f6c13b335110f8f7ae5f28740a8d0f933a7451ad46ca4b470b2d84f9aa490798e1a6b37bc04299084509d1043fb3cc9185b32fa18afdd2caf6a8d780bef454beabeb64c87ae0cd0e0dc880671965952592e365ddbd1fb9005d9017b822cfc6ff9603603f66a69e0e128e9a01e327683a6235fa14bd17744f0eb4ea425648ee0eb81960d1499c1aa3bdfca7d92e45de6aa5497375b77e2e7beff96dd90658c045eacdd0fd03209b0df854438f8702a85ce1ddca2673899f987a37ad6ba97d5e5164a63dd2baa633914878b1f25623142d21b53cc866030621d1c54a4f4fed27ef538cd58d0cd9a744799fdd2403d84654b5b29c78e97e0cbd9a5d23889a64252ea70871d63f692c2ba763b0d79e69bdddfde543b2a9d96b1f91c1ddcd64da610c4e98202e6e5fcba0d4c008475b86b1a632f53dc1babb56a4fe0db65854d6aa14f4ad4a680f042567d23fd6b2e14aafa1240687dca7dca840a7d9123a87fd74afb38676da0ab9c1eeebdd036907e10a51e76344b6d434426ee9e9d213dde3cbfcbd51cfdfe9abb0e8597ff99d0050bf144c4d18d42fd7b1a6df5898a4199702a0';
                // dd($this->CCdecrypt($enctdata, env('workingKey')));
                // dd(\DB::getQueryLog());
                if ($updateResult) {

                    $data['encRequest'] = $encrypted_data;
                    $data['action_url'] = env('ccaction_url') . 'command=initiateTransaction';
                    $data['access_code'] = env('accessCode');

                    $response = ['status' => 200, 'message' => 'CCAvenue payment success', 'data' => $data];
                    goto returnFVI;
                } else {
                    $response = ['status' => 400, 'message' => 'The process failed!', 'error' => 'The process failed!'];
                    goto returnFVI;
                }
            } else {
                $response = ['status' => 400, 'message' => 'The Kindly Contact Admin!', 'error' => 'The Kindly Contact Admin!'];
                goto returnFVI;
            }



            returnFVI:
            return response()->json($response);
        } catch (Exception $e) {

            $response = ['status' => 500, 'message' => 'Throw in Catch Section', 'error' => ['message' => $e->getMessage(), 'code' => $e->getCode(), 'string' => $e->__toString()]];
            return response()->json($response);
        }
    }



    public function ccavenueSuccess(Request $request)
    {

        try {
            $response = [];
            $input = $request->all();
            $ses_user = Session::get('user');

            if ($request->encResp == '' || $request->encResp == null || $request->encResp == 'null') {
                $response = ['status' => 'failed', 'message' => 'Please use a valid encResponse!', 'error' => 'Please use a valid encResponse!'];
                goto returnFVI;
            }

            $encResponse = $request->encResp;
            $data = [];

            // Get User ID
            $user_id = $ses_user[0]->ID;
            if ($user_id == '' || $user_id == null || $user_id == 'null') {
                $response = ['status' => 'failed', 'message' => 'Login Required!', 'error' => 'Kindly check the access token!'];
                goto returnFVI;
            }

            if ($encResponse == '' || $encResponse == null || $encResponse == 'null') {
                $response = ['status' => 'failed', 'message' => 'Order Response Required!', 'error' => 'Kindly check the Order Response!'];
                goto returnFVI;
            }

            if ($encResponse != '') {

                $rcvdString = $this->CCdecrypt($encResponse, env('workingKey'));
                $order_status = "";
                parse_str($rcvdString, $res);
                if (count($res) > 0) {
                    $tran_id = $res['order_id'];

                    $data['order_id'] = $tran_id;

                    $payment_history = DB::table('payment_history')->select('tid', 'id', 'finaltotal', 'pay_status', 'pay_type')->where('user_id', $user_id)->where('tid', 'LIKE', $tran_id)->orderBy('id', 'desc')->limit(1)->get();

                    if ($payment_history->count() > 0) {

                        $order_status = $res['order_status'];
                        $surcharge = $res['amount'] - $res['mer_amount'];
                        $pay_trans_array = array("pay_re_status" => $order_status, "enc_response" => $rcvdString, "pay_response" => json_encode($res), "response_ref" => $encResponse, "surcharge" => $surcharge != "null" ? $surcharge : '0.0', "trans_fee" => $res['trans_fee'] != "null" ? $res['trans_fee'] : '0.0', "service_tax" => $res['service_tax'] != "null" ? $res['service_tax'] : '0.0');
                        $updateResult = DB::table('payment_history')->where('tid', $tran_id)
                            ->where('id', $payment_history[0]->id)
                            ->update($pay_trans_array);

                        // return $updateResult;

                        if ($updateResult) {

                            if ($order_status == "Success") {
                                if ($payment_history[0]->pay_type == 'member') {
                                    $curl = curl_init();
                                    $use_token = $request->token;
                                    $send_dt = json_encode([
                                        "token" => $use_token,
                                        "device_id" => 1
                                    ]);
                                    curl_setopt_array($curl, array(
                                        CURLOPT_URL => 'http://kinchitapi.senthil.in.net/api/become-a-member',
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_ENCODING => '',
                                        CURLOPT_MAXREDIRS => 10,
                                        CURLOPT_TIMEOUT => 0,
                                        CURLOPT_FOLLOWLOCATION => true,
                                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                        CURLOPT_CUSTOMREQUEST => 'POST',
                                        CURLOPT_POSTFIELDS => $send_dt,
                                        CURLOPT_HTTPHEADER => array(
                                            'Content-Type: application/json'
                                        ),
                                    ));

                                    $response = curl_exec($curl);

                                    curl_close($curl);
                                    $json = json_decode($response);
                                } elseif ($payment_history[0]->pay_type == 'member-renewal') {
                                    $curl = curl_init();
                                    $use_token = $request->token;
                                    $send_dt = json_encode([
                                        "token" => $use_token,
                                        "device_id" => 1
                                    ]);
                                    curl_setopt_array($curl, array(
                                        CURLOPT_URL => 'http://kinchitapi.senthil.in.net/api/member-renewal',
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_ENCODING => '',
                                        CURLOPT_MAXREDIRS => 10,
                                        CURLOPT_TIMEOUT => 0,
                                        CURLOPT_FOLLOWLOCATION => true,
                                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                        CURLOPT_CUSTOMREQUEST => 'POST',
                                        CURLOPT_POSTFIELDS => $send_dt,
                                        CURLOPT_HTTPHEADER => array(
                                            'Content-Type: application/json'
                                        ),
                                    ));

                                    $response = curl_exec($curl);

                                    curl_close($curl);
                                    $json = json_decode($response);
                                } else {
                                    $json = '';
                                }
                                // return $order_status;
                                $data['order_status'] = $order_status;

                                $finalupdate = DB::table('payment_history')->where('tid', $tran_id)
                                    ->where('id', $payment_history[0]->id)
                                    ->update(["pay_status" => 'success', "pay_re_status" => $order_status]);
                                if ($finalupdate) {
                                    $userdetails = DB::table('userprofile_dt')->where('user_id', $ses_user[0]->ID)->first();
                                    $paymentoption = [
                                        'first_name' => $userdetails->first_name,
                                        'last_name' => $userdetails->last_name,
                                        'user_email' => $userdetails->user_email,
                                        'phone_number' => $userdetails->phone_number
                                    ];
                                    Mail::to($userdetails->user_email)->send(new MyMail(null, null, null, null, null, null, null, null, null, null, null, null, null, $paymentoption));
                                    $response = ['status' => 200, 'message' => 'CCAvenue payment success', 'data' => $data, "request" => $json, 'userdetails' => $paymentoption];
                                    //$response = ['status' => 200, 'message' => 'CCAvenue payment success', 'data' => $data, "request" => $json];
                                    goto returnFVI;
                                } else {
                                    $response = ['status' => 400, 'message' => 'Final update failed', 'data' => $data];
                                    goto returnFVI;
                                }
                            } else if ($order_status == "Failure" || $order_status == "Aborted") {
                                // return $order_status;
                                $ccRequest = [
                                    "order_no" => $tran_id,
                                ];
                                $encrypted_data = $this->CCencrypt(json_encode($ccRequest), env('workingKey'));

                                $ccURL = env('ccCheckin_url') . 'enc_request=' . $encrypted_data . '&access_code=' . env('accessCode') . '&command=orderStatusTracker&request_type=JSON&response_type=JSON&version=1.1';
                                $responseData = $this->run_Api('POST', $ccURL);
                                parse_str($responseData, $jsonData);
                                // return $responseData;
                                // dd($responseData);

                                $status = (int) $jsonData['status'];
                                if ($status === 0) {
                                    $encResponse = trim($jsonData['enc_response']);
                                    $rcvdString = $this->CCdecrypt($encResponse, env('workingKey'));

                                    $ccResponse = json_decode($rcvdString, true);
                                    $order_status_n = $ccResponse['order_status'];

                                    if ($order_status_n != '') {
                                        if ($order_status_n == 'Successful' || $order_status_n == 'Shipped') {
                                            $data['order_status'] = $order_status;

                                            $finalupdate = DB::table('payment_history')->where('tid', $tran_id)
                                                ->where('id', $payment_history[0]->id)
                                                ->update(["pay_status" => 'failed', "pay_re_status" => $order_status]);
                                            if ($finalupdate) {
                                                $response = ['status' => 200, 'message' => 'CCAvenue payment success', 'data' => $data];
                                                goto returnFVI;
                                            } else {
                                                $response = ['status' => 400, 'message' => 'Final update failed', 'data' => $data];
                                                goto returnFVI;
                                            }
                                        } else if ($order_status_n == 'Awaited') {
                                            $response = ['status' => 400, 'message' => 'CCAvenue payment failed1', 'error' => ['redirectURL' => env('Base_URL') . 'failed/', 'paymentState' => $order_status_n]];
                                            goto returnFVI;
                                        } else if ($order_status_n == 'Aborted') {
                                            $response = ['status' => 400, 'message' => 'CCAvenue payment failed2', 'error' => ['redirectURL' => env('Base_URL') . 'failed/', 'paymentState' => $order_status_n]];
                                            goto returnFVI;
                                        } else {
                                            $response = ['status' => 400, 'message' => 'CCAvenue payment failed3', 'error' => ['redirectURL' => env('Base_URL') . 'failed/']];
                                            goto returnFVI;
                                        }
                                    }
                                } else {
                                    $finalupdate = DB::table('payment_history')->where('tid', $tran_id)
                                        ->where('id', $payment_history[0]->id)
                                        ->update(["pay_status" => 'failed', "pay_re_status" => $order_status]);
                                    if ($finalupdate) {
                                        $response = ['status' => 400, 'message' => 'CCAvenue payment failed4', 'data' => $data];
                                        goto returnFVI;
                                    } else {
                                        $response = ['status' => 400, 'message' => 'Final update failed', 'data' => $data];
                                        goto returnFVI;
                                    }
                                }
                            } else {
                                $response = ['status' => 400, 'message' => 'CCAvenue payment failed5', 'error' => ['redirectURL' => env('Base_URL') . 'failed/']];
                                goto returnFVI;
                            }
                        } else {
                            $response = ['status' => 400, 'message' => 'CCAvenue payment failed6', 'error' => ['redirectURL' => env('Base_URL') . 'failed/']];
                            goto returnFVI;
                        }
                        // } else {
                        //     $response = ['status' => 'failed', 'message' => 'CCAvenue payment failed', 'error' => ['redirectURL' => env('Base_URL') . 'failed/']];
                        //     goto returnFVI;
                        // }
                    } else {
                        $response = ['status' => 400, 'message' => 'The payment track missing!', 'error' => 'the payment track missing!'];
                        goto returnFVI;
                    }
                }
            }


            returnFVI:
            return response()->json($response);
        } catch (Exception $e) {

            $response = ['status' => 'failed', 'message' => 'Throw in Catch Section', 'error' => ['message' => $e->getMessage(), 'code' => $e->getCode(), 'string' => $e->__toString()]];
            return response()->json($response);
        }
    }


    public function ccavenueStatus(Request $request)
    {
        try {
            $ses_user = Session::get('user');
            $paymentDetails = DB::table('payment_history')->where('tid', $request->t_id)->first();
            $user = DB::table('wpu6_users')->where('ID', $paymentDetails->user_id)->first();

            return response()->json([
                'status' => 200,
                'data' =>  $paymentDetails,
                'userdetails' =>  $user,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
