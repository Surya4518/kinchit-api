<!DOCTYPE html>
<html>

<head>
    <title>My Email</title>
</head>

<body>
@if(isset($data['name']))
    @if($data['device_id']=='1')
    <h1>Dear {{ $data['name'] }},</h1>
    <p style="font-size: large;">We received a request to reset the password for your Kinchitkaram account. To proceed with the password reset,</p>
    <p style="font-size: large;">please click on the link below: <br> <b style="color: blue;">Click here: http://kinchit-front.senthil.in.net/changepassword/{{ $data['token'] }}</b></p>
    <p style="font-size: large;">If you did not request a password reset, please ignore this email, and your account will remain secure.</p>
    <p style="font-size: large;">Thank you for using Kinchitkaram.</p>
    <p style="font-size: large;">Best regards,</p>
    <p style="font-size: large;">Kinchitkaram Trust</p>
    @else
    <h1>Dear {{ $data['name'] }},</h1>
    <p style="font-size: large;">We received a request to reset the password for your Kinchitkaram account. To proceed with the password reset,</p>
    <p style="font-size: large;"><b style="color: blue;">Otp : {{ $data['token'] }}</b></p>
    <p style="font-size: large;">If you did not request a password reset, please ignore this email, and your account will remain secure.</p>
    <p style="font-size: large;">Thank you for using Kinchitkaram.</p>
    <p style="font-size: large;">Best regards,</p>
    <p style="font-size: large;">Kinchitkaram Trust</p>
    @endif
    @elseif(isset($arr['otp']))
    <h1>Dear {{ $arr['display_name'] }},</h1>
    <p style="font-size: large;">Welcome to Kinchitkaram Trust!</p>
    <p style="font-size: large;">Here is your One-Time Password to register your account and join us.</p>
    <p style="font-size: large;">OTP: <b style="color: blue;">{{ $arr['otp'] }}</b></p>
    <p style="font-size: large;">Thank you for choosing Kinchitkaram.</p>
    <p style="font-size: large;">Best regards,</p>
    <p style="font-size: large;">Kinchitkaram Trust, Old No 6,</p>
    <p style="font-size: large;">Bheemasena Garden Street, </p>
    <p style="font-size: large;">Mylapore, Chennai – 600 004, </p>
    <p style="font-size: large;">Tamil Nadu, India</p>
    <p style="font-size: large;">Phone: 044 - 24992728</p>
    @elseif(isset($member) && isset($member['display_name']) && isset($password))
    <h1>Dear {{ $member['display_name'] }},</h1>
    <p style="font-size: large;">User Name: <b>{{ $member['display_name'] }}</b></p>
    <p style="font-size: large;">Password: <b>{{ $password }}</b></p>
    @elseif(isset($contactus['name']))
    <h1>Dear {{ $contactus['name'] }},</h1>
    <p style="font-size: large;">Name: <b>{{ $contactus['name'] }}</b></p>
    <p style="font-size: large;">Email: <b>{{ $contactus['email'] }}</b></p>
    <p style="font-size: large;">Message: <b>{{ $contactus['message'] }}</b></p>
    @elseif(isset($conformail['FromID']))
    <p style="font-size: large;">From Id: <b>{{ $conformail['FromID'] }}</b></p>
    <p style="font-size: large;">To Id: <b>{{ $conformail['ToId'] }}</b></p>
    <p style="font-size: large;">Message: <b>{{ $conformail['Msg'] }}</b></p>
    @elseif(isset($gnanakaithaa['name_of_student']))
    <p style="font-size: large;">User Name: <b>{{ $gnanakaithaa['name_of_student'] }}</b></p>
    <p style="font-size: large;">contact No: <b>{{ $gnanakaithaa['contact_no'] }}</b></p>
    <p style="font-size: large;">Email: <b>{{ $gnanakaithaa['email'] }}</b></p>
    @elseif(isset($assigncourse['name_of_student']))
    <p style="font-size: large;">User Name: <b>{{ $assigncourse['name_of_student'] }}</b></p>
    <p style="font-size: large;">contact No: <b>{{ $assigncourse['phone_number'] }}</b></p>
    <p style="font-size: large;">Email: <b>{{ $assigncourse['email'] }}</b></p>
    @elseif(isset($matri['Name']))
    <p style="font-size: large;">User Name: <b>{{ $matri['Name'] }}</b></p>
    <p style="font-size: large;">contact No: <b>{{ $matri['Mobile'] }}</b></p>
    <p style="font-size: large;">Email: <b>{{ $matri['ConfirmEmail'] }}</b></p>
    @elseif(isset($userdetails['user_login']))
    <p style="font-size: large;">User Name: <b>{{ $userdetails['user_login'] }}</b></p>
    <p style="font-size: large;"> <b>Your Approval Request is Successfully</b></p>
    @elseif(isset($addmember['Name']))
    <p style="font-size: large;">User Name: <b>{{ $addmember['first_name'] }}{{ $addmember['last_name'] }}</b></p>
    <p style="font-size: large;">contact No: <b>{{ $addmember['phone_number'] }}</b></p>
    <p style="font-size: large;">Email: <b>{{ $addmember['user_email'] }}</b></p>
    @elseif(isset($depositdetails['first_name']))
    <p style="font-size: large;">User Name: <b>{{ $depositdetails['first_name'] }}{{ $depositdetails['last_name'] }}</b></p>
    <p style="font-size: large;">contact No: <b>{{ $depositdetails['phone_number'] }}</b></p>
    <p style="font-size: large;">Email: <b>{{ $depositdetails['user_email'] }}</b></p>
    @elseif(isset($changevoluteer['first_name']))
    <p style="font-size: large;">User Name: <b>{{ $changevoluteer['first_name'] }}{{ $changevoluteer['last_name'] }}</b></p>
    <p style="font-size: large;">contact No: <b>{{ $changevoluteer['phone_number'] }}</b></p>
    <p style="font-size: large;">Email: <b>{{ $changevoluteer['user_email'] }}</b></p>
    @elseif(isset($paymentoption['first_name']))
    <p style="font-size: large;">User Name: <b>{{ $paymentoption['first_name'] }}{{ $paymentoption['last_name'] }}</b></p>
    <p style="font-size: large;">contact No: <b>{{ $paymentoption['phone_number'] }}</b></p>
    <p style="font-size: large;">Email: <b>{{ $paymentoption['user_email'] }}</b></p>
    @else
    <h1>Dear {{ $updatepassword['display_name'] }},</h1>
    <p style="font-size: large;">User Name: <b>{{ $updatepassword['display_name'] }}</b></p>
    <p style="font-size: large;">Password: <b>{{ $updatepassword['password'] }}</b></p>
    @endif
</body>

</html>