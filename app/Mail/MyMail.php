<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
class MyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $arr;
    public $member;
    public $contactus;
    public $updatepassword;
    public $password;
    public $conformail;
    public $gnanakaithaa;
    public $assigncourse;
    public $matri;
    public $userdetails;
    public $addmember;
    public $depositdetails;
    public $changevoluteer;
    public $paymentoption;

    public function __construct($data = null, $arr = null, $member = null, $contactus = null, $updatepassword = null, $password = null, $conformail = null,$gnanakaithaa = null,$assigncourse=null,$matri=null,$userdetails=null
    ,$addmember = null,$depositdetails = null,$changevoluteer = null,$paymentoption = null)
{
    $this->data = $data;
    $this->arr = $arr;
    $this->member = $member;
    $this->contactus = $contactus;
    $this->updatepassword = $updatepassword;
    $this->password = $password;
    $this->conformail = $conformail;
    $this->gnanakaithaa = $gnanakaithaa;
    $this->assigncourse = $assigncourse;
    $this->matri = $matri;
    $this->userdetails = $userdetails;
    $this->addmember = $addmember;
    $this->depositdetails = $depositdetails;
    $this->changevoluteer = $changevoluteer;
    $this->paymentoption = $paymentoption;
}
    public function build()
    {
        if ($this->data) {
            return $this->subject('Reset Your Kinchitkaram Account Password!!')
                ->view('emails.my_mail')
                ->with(['data' => $this->data]);
        } elseif ($this->arr) {
            return $this->subject('Kinchitkaram Trust - OTP Verification')
                ->view('emails.my_mail')
                ->with(['otp' => $this->arr]);
        } elseif ($this->member) {
            return $this->subject('User Verfication')
                ->view('emails.my_mail')
                ->with(['member' => $this->member]);
        } elseif ($this->contactus) {
            return $this->subject('Contact Us')
                ->view('emails.my_mail')
                ->with(['contactus' => $this->contactus]);
        } elseif($this->updatepassword) {
            return $this->subject('Updated Password')
                ->view('emails.my_mail')
                ->with(['updatepassword' => $this->updatepassword]);
        }elseif($this->conformail) {
            return $this->subject('Send Mail')
                ->view('emails.my_mail')
                ->with(['conformmail' => $this->conformail]);
        }elseif($this->gnanakaithaa) {
            return $this->subject('Gnanakaithaa ')
                ->view('emails.my_mail')
                ->with(['gnanakaithaa' => $this->gnanakaithaa]);
        }elseif($this->assigncourse) {
            return $this->subject('Assign Course')
                ->view('emails.my_mail')
                ->with(['assigncourse' => $this->assigncourse]);
        }elseif($this->matri) {
            return $this->subject('Matri')
                ->view('emails.my_mail')
                ->with(['matri' => $this->matri]);
        }elseif($this->userdetails) {
            return $this->subject('Become a Volunteer')
                ->view('emails.my_mail')
                ->with(['userdetails' => $this->userdetails]);
        }elseif($this->addmember) {
            return $this->subject('Add a Member')
                ->view('emails.my_mail')
                ->with(['addmember' => $this->addmember]);
        }elseif($this->depositdetails) {
            return $this->subject('Deposit Details')
                ->view('emails.my_mail')
                ->with(['depositdetails' => $this->depositdetails]);
        }elseif($this->changevoluteer) {
            return $this->subject('Change Volunteer')
                ->view('emails.my_mail')
                ->with(['changevoluteer' => $this->changevoluteer]);
        }elseif($this->paymentoption) {
            return $this->subject('Payment Success')
                ->view('emails.my_mail')
                ->with(['paymentoption' => $this->paymentoption]);
        }
    }
}