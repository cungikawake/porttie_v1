<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use Mail;
class VoucherController extends Controller
{
    public function sendmail(Request $request){
         
        $guest = 'guest3@mailinator.com';
        $name = 'putu';
        $title = 'yes ok yes';
        $data['name'] = 'name';
        $pdf = PDF::loadView('mail.invoice.voucher_pdf', $data)->setPaper('a4'); 
        return $pdf->download('laporan-pegawai-pdf.pdf');
          
        try{
            $data = array(
                'name' => 'putu',
                'phone' => '098755',
                'email' => $guest, 
                'bodyMessage' => 'pdf'
            );

            $pdf = PDF::loadView('mail.invoice.voucher_pdf', $data)->setPaper('a4'); 
            Mail::send([], [], function ($message)  use ($guest,$name, $title) {
                $message->to($guest)
                  ->subject('Accept Purchase - porttie.com') 
                  ->attachData($pdf->output(), 'voucher1.pdf', [
                    'mime' => 'application/pdf',
                    ])
                  ->setBody('<h3>Hello '.$name.'</h3>
                  <p>Your order/request for the product <b>'.$title.'</b> been accepted and will be processed shortly. Please contact the seller for any enquiries.</p>
                  <p>
                      <a href="'.route('account.purchase-history.index').'">Purchase History</a>
                  </p>
                  <br>
                  Thanks,<br>', 'text/html'); // for HTML rich messages
            });
        }catch(JWTException $exception){
            $this->serverstatuscode = "0";
            $this->serverstatusdes = $exception->getMessage();
        }

        if (Mail::failures()) {
             $this->statusdesc  =   "Error sending mail";
             $this->statuscode  =   "0";

        }else{

           $this->statusdesc  =   "Message sent Succesfully";
           $this->statuscode  =   "1";
        }
        return response()->json(compact('this'));
    }
    
}
