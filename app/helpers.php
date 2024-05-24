<?php

    function SendSMS(){
        $curl = curl_init();

         curl_setopt_array($curl, array(
           CURLOPT_URL => 'http://api.nsite.in/api/v2/SendSMS?SenderId=KINCIT&Is_Unicode=false&Is_Flash=false&Message=Welcome%20to%20Kinchitkaram%20Trust.%20Your%20volunteer%20code%20is%20{%23test%23}.%20Below%20are%20your%20login%20details%20-%20kinchit.org%2Flogin.%20Your%20username%20and%20password%20is%20{%23var%23}&MobileNumbers=9345553521&ApiKey=crWZ3EezAeJIZW3qDgPeOUq2jQYpGlGUQk7GQi%2Bwrpo%3D&ClientId=6efe925d-12c7-49ba-a649-b4bbe3d2d93d',
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => '',
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 0,
           CURLOPT_FOLLOWLOCATION => true,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => 'GET',
         ));
         
         $response = curl_exec($curl);
         
         curl_close($curl);
        //  echo $response;
        return json_decode($response);
    }

    function SendPushNotification($userId = NULL, $message = NULL, $title = NULL){
        dd($userId);
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        
        $fcmToken = \Illuminate\Support\Facades\DB::table('wpu6_users')
            ->where('ID', '=', $userId)
            ->value('fcm_token');
    
        if (!$fcmToken) {
            return false;
        }
    
        $notification = [
            'title' => $title ?: 'Test',
            'body' => $message
        ];
    
        $extraData = [
            'message' => $notification,
            'moredata' => 'Nothing'
        ];
    
        $fcmNotification = [
            'to' => $fcmToken,
            'notification' => $notification,
            'data' => $extraData
        ];
    
        $headers = [
            'Authorization: key=' . env('FIREBASE_API_KEY'),
            'Content-Type: application/json'
        ];
    
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
    
        $result = json_decode(curl_exec($ch));
        $error = curl_error($ch); // Check for cURL errors
        curl_close($ch);
    
        if ($error) {
            return false;
        }
    
        return isset($result->success) ? $result->success : false;
    }
?>
