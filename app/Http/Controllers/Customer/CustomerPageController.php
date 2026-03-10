<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Flavor;
use App\Models\Feedback;

class CustomerPageController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function home()
    {
        $flavors = Flavor::orderBy('created_at', 'desc')->get();
        $feedbacks = Feedback::orderBy('feedback_date', 'desc')->get();
        return view('Customer.home', compact('flavors', 'feedbacks'));
    }

    public function about()
    {
        return view('customer.aboutus');
    }

    public function Customerlogin()
    {
        return view('customer.login');
    }

    public function register()
    {
        return view('customer.register');
    }
}
