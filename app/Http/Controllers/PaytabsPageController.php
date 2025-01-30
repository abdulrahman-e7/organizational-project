<?php

namespace App\Http\Controllers;

use Paytabscom\Laravel_paytabs\Facades\paypage;

class PaytabsPageController extends Controller
{

    public function index()
    {
        $pay = paypage::sendPaymentCode('all')
            ->sendTransaction('sale', 'ecom')
            ->sendCart(10, 1000, 'test')
            ->sendCustomerDetails('Walaa Elsaeed', 'w.elsaeed@paytabs.com', '0101111111', 'test', 'Nasr City', 'Cairo', 'UAE', '1234', '100.279.20.10')
            ->sendShippingDetails('Walaa Elsaeed', 'w.elsaeed@paytabs.com', '0101111111', 'test', 'Nasr City', 'Cairo', 'UAE', '1234', '100.279.20.10')
            ->sendURLs(env('APP_URL'), env('APP_URL'))
            ->sendLanguage('en')
            ->create_pay_page();

        return $pay;
    }
}
