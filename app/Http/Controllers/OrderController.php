<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;
use App\User;
use PDF;
use Illuminate\Support\Facades\Notification;
use Helper;
use Illuminate\Support\Str;
use App\Notifications\StatusNotification;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::orderBy('id', 'DESC')->paginate(10);
        return view('backend.order.index')->with('orders', $orders);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // {
    //     $this->validate($request, [
    //         'first_name' => 'string|required',
    //         'last_name' => 'string|required',
    //         'address1' => 'string|required',
    //         'address2' => 'string|nullable',
    //         'coupon' => 'nullable|numeric',
    //         'phone' => 'numeric|required',
    //         'post_code' => 'string|nullable',
    //         'email' => 'string|required'
    //     ]);
    //     // return $request->all();

    //     if (empty(Cart::where('user_id', auth()->user()->id)->where('order_id', null)->first())) {
    //         request()->session()->flash('error', 'Cart is Empty !');
    //         return back();
    //     }

    //     $order                      = new Order();
    //     $order_data                 = $request->all();
    //     $order_data['order_number'] = 'ORD-' . strtoupper(Str::random(10));
    //     $order_data['user_id']      = $request->user()->id;
    //     $order_data['shipping_id']  = $request->shipping;
    //     $shipping                   = Shipping::where('id', $order_data['shipping_id'])->pluck('price');
    //     $order_data['sub_total']    = Helper::totalCartPrice();
    //     $order_data['quantity']     = Helper::cartCount();
    //     if (session('coupon')) {
    //         $order_data['coupon'] = session('coupon')['value'];
    //     }
    //     if ($request->shipping) {
    //         if (session('coupon')) {
    //             $order_data['total_amount'] = Helper::totalCartPrice() + $shipping[0] - session('coupon')['value'];
    //         } else {
    //             $order_data['total_amount'] = Helper::totalCartPrice() + $shipping[0];
    //         }
    //     } else {
    //         if (session('coupon')) {
    //             $order_data['total_amount'] = Helper::totalCartPrice() - session('coupon')['value'];
    //         } else {
    //             $order_data['total_amount'] = Helper::totalCartPrice();
    //         }
    //     }
    //     $order_data['status'] = "new";
    //     if (request('payment_method') == 'paypal') {
    //         $order_data['payment_method'] = 'paypal';
    //         $order_data['payment_status'] = 'paid';
    //     } else {
    //         $order_data['payment_method'] = 'cod';
    //         $order_data['payment_status'] = 'Unpaid';
    //     }
    //     $order->fill($order_data);
    //     $status = $order->save();
    //     if ($order)
    //         $users = User::where('role', 'admin')->first();
    //     $details = [
    //         'title' => 'New order created',
    //         'actionURL' => route('order.show', $order->id),
    //         'fas' => 'fa-file-alt'
    //     ];
    //     Notification::send($users, new StatusNotification($details));
    //     if (request('payment_method') == 'paypal') {
    //         return redirect()->route('payment')->with(['id' => $order->id]);
    //     } else {
    //         session()->forget('cart');
    //         session()->forget('coupon');
    //     }
    //     Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => $order->id]);

    //     request()->session()->flash('success', 'Your product successfully placed in order');
    //     return redirect()->route('home');
    // }


    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'address1' => 'string|required',
            'city' => 'string|required',
            'coupon' => 'nullable|numeric',
            'phone' => 'numeric|required',
            'post_code' => 'string|nullable',
            'email' => 'string|required'
        ]);

        if (empty(Cart::where('user_id', auth()->user()->id)->where('order_id', null)->first())) {
            request()->session()->flash('error', 'Cart is Empty !');
            return back();
        }

        $order                      = new Order();
        $order_data                 = $request->all();
        $order_data['order_number'] = 'ORD-' . strtoupper(Str::random(10));
        $order_data['user_id']      = $request->user()->id;
        $order_data['shipping_id']  = $request->shipping;
        $shipping                   = Shipping::where('id', $order_data['shipping_id'])->pluck('price');
        $order_data['sub_total']    = Helper::totalCartPrice();
        $order_data['quantity']     = Helper::cartCount();
        if (session('coupon')) {
            $order_data['coupon'] = session('coupon')['value'];
        }
        if ($request->shipping) {
            if (session('coupon')) {
                $order_data['total_amount'] = Helper::totalCartPrice() + $shipping[0] - session('coupon')['value'];
            } else {
                $order_data['total_amount'] = Helper::totalCartPrice() + $shipping[0];
            }
        } else {
            if (session('coupon')) {
                $order_data['total_amount'] = Helper::totalCartPrice() - session('coupon')['value'];
            } else {
                $order_data['total_amount'] = Helper::totalCartPrice();
            }
        }
        $order_data['status'] = "new";
        if (request('payment_method') == 'paypal') {
            $order_data['payment_method'] = 'paypal';
            $order_data['payment_status'] = 'paid';
            $order->fill($order_data);
            $status = $order->save();
            if ($status) {
                $users   = User::where('role', 'admin')->first();
                $details = [
                    'title' => 'New order created',
                    'actionURL' => route('order.show', $order->id),
                    'fas' => 'fa-file-alt'
                ];
                Notification::send($users, new StatusNotification($details));
                session()->forget('cart');
                session()->forget('coupon');
                request()->session()->flash('success', 'Your product successfully placed in order');
                return redirect()->route('home');
            } else {
                request()->session()->flash('error', 'Error occurred while processing the order');
                return back();
            }
        } else if (request('payment_method') == 'payu') {
            // Log::INFO($order_data);

            // $apiKey = 'vsS6QO63Lv5VSmPqkEx97HpgiZ'; // el bueno
            $apiKey = '4Vj8eK4rloUd272L48hsrarnUA';
            $apiLogin = 'jjDFv5QzH8T7Fd4';
            // $merchanId = '1007859'; //el bueno
            $merchanId = '508029';
            $signature = md5($apiKey."~".$merchanId."~".$order_data['order_number']."~".$order_data['total_amount']."~COP");
            Log::INFO($signature);

                // '_token' => 'EBarOlUrG6iKS6l1byijYToWR2kC07VtRWUqXQi1',
                // 'first_name' => 'diego',
                // 'last_name' => 'pacheco',
                // 'email' => 'diegojose2322@hotmail.com',
                // 'phone' => '3008348151',
                // 'country' => 'CO',
                // 'address1' => 'diagonal 29 # 30-28',
                // 'address2' => NULL,
                // 'post_code' => '083002',
                // 'shipping' => '1',
                // 'payment_method' => 'payu',
                // 'order_number' => 'ORD-YZWVLMUJMZ',
                // 'user_id' => 1,
                // 'shipping_id' => '1',
                // 'sub_total' => 44324.0,
                // 'quantity' => '1',
                // 'total_amount' => 59324.0,
                // 'status' => 'new',


            $payuFormData = [
                'merchantId' => $merchanId,
                'referenceCode' => $order_data['order_number'],
                'accountId' => '512321',
                'description' => 'Pago la media agua PayU',
                'currency' => 'COP',
                'amount' => $order_data['total_amount'],
                'tax' => '0',
                'taxReturnBase' => '0',
                'signature' => $signature,
                'buyerEmail' => $order_data['email'] ,
                'test' => '1',
                "shippingAddress" =>  $order_data['address1'] ,
                "shippingCity" =>  $order_data['city'],
                "shippingCountry" =>  $order_data['country'],
                'responseUrl' => 'https://google.com',
                'confirmationUrl' => 'https://youtube.com',
            ];
            Log::INFO(json_encode($payuFormData));

            // Devolver la vista con el formulario de PayU
            return view('frontend.auto_submit_payu_form')->with('payuFormData', $payuFormData);
        } else {
            $order_data['payment_method'] = 'cod';
            $order_data['payment_status'] = 'Unpaid';
            $order->fill($order_data);
            $status = $order->save();
            if ($order)
                $users = User::where('role', 'admin')->first();
            $details = [
                'title' => 'Arremangala arrempujala',
                'actionURL' => route('order.show', $order->id),
                'fas' => 'fa-file-alt'
            ];
            Notification::send($users, new StatusNotification($details));
            if (request('payment_method') == 'paypal') {
                return redirect()->route('payment')->with(['id' => $order->id]);
            } else {
                session()->forget('cart');
                session()->forget('coupon');
            }
            Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => $order->id]);

            request()->session()->flash('success', 'Your product successfully placed in order');
            return redirect()->route('home');
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::find($id);
        return view('backend.order.show')->with('order', $order);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order = Order::find($id);
        return view('backend.order.edit')->with('order', $order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        $this->validate($request, [
            'status' => 'required|in:new,process,delivered,cancel'
        ]);
        $data = $request->all();
        if ($request->status == 'delivered') {
            foreach ($order->cart as $cart) {
                $product        = $cart->product;
                $product->stock -= $cart->quantity;
                $product->save();
            }
        }
        $status = $order->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Successfully updated order');
        } else {
            request()->session()->flash('error', 'Error while updating order');
        }
        return redirect()->route('order.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        if ($order) {
            $status = $order->delete();
            if ($status) {
                request()->session()->flash('success', 'Order Successfully deleted');
            } else {
                request()->session()->flash('error', 'Order can not deleted');
            }
            return redirect()->route('order.index');
        } else {
            request()->session()->flash('error', 'Order can not found');
            return redirect()->back();
        }
    }

    public function orderTrack()
    {
        return view('frontend.pages.order-track');
    }

    public function productTrackOrder(Request $request)
    {
        $order = Order::where('user_id', auth()->user()->id)->where('order_number', $request->order_number)->first();
        if ($order) {
            if ($order->status == "new") {
                request()->session()->flash('success', 'Your order has been placed. please wait.');
                return redirect()->route('home');

            } elseif ($order->status == "process") {
                request()->session()->flash('success', 'Your order is under processing please wait.');
                return redirect()->route('home');

            } elseif ($order->status == "delivered") {
                request()->session()->flash('success', 'Your order is successfully delivered.');
                return redirect()->route('home');

            } else {
                request()->session()->flash('error', 'Your order canceled. please try again');
                return redirect()->route('home');

            }
        } else {
            request()->session()->flash('error', 'Invalid order numer please try again');
            return back();
        }
    }

    // PDF generate
    public function pdf(Request $request)
    {
        $order = Order::getAllOrder($request->id);
        // return $order;
        $file_name = $order->order_number . '-' . $order->first_name . '.pdf';
        // return $file_name;
        $pdf = PDF::loadview('backend.order.pdf', compact('order'));
        return $pdf->download($file_name);
    }
    // Income chart
    public function incomeChart(Request $request)
    {
        $year = \Carbon\Carbon::now()->year;
        // dd($year);
        $items = Order::with(['cart_info'])->whereYear('created_at', $year)->where('status', 'delivered')->get()
            ->groupBy(function ($d) {
                return \Carbon\Carbon::parse($d->created_at)->format('m');
            });
        // dd($items);
        $result = [];
        foreach ($items as $month => $item_collections) {
            foreach ($item_collections as $item) {
                $amount = $item->cart_info->sum('amount');
                // dd($amount);
                $m = intval($month);
                // return $m;
                isset($result[$m]) ? $result[$m] += $amount : $result[$m] = $amount;
            }
        }
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthName        = date('F', mktime(0, 0, 0, $i, 1));
            $data[$monthName] = (! empty($result[$i])) ? number_format((float) ($result[$i]), 2, '.', '') : 0.0;
        }
        return $data;
    }
}
