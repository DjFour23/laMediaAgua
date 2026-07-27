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

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Traemos todos los pedidos sin paginar para que DataTables gestione el filtrado y paginación en JS
        $orders = Order::orderBy('id', 'DESC')->get();
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
    public function store(Request $request)
    {
        // Validación: solo permitimos 'breb' o 'cod'
        $this->validate($request, [
            'first_name'        => 'string|required',
            'last_name'         => 'string|required',
            'address1'          => 'string|required',
            'city'              => 'string|required',
            'coupon'            => 'nullable|numeric',
            'phone'             => 'numeric|required',
            'post_code'         => 'string|nullable',
            'email'             => 'string|required',
            'payment_method'    => 'required|in:breb,cod',
            'payment_reference' => 'required_if:payment_method,breb|nullable|string',
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

        if ($request->shipping && count($shipping) > 0) {
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

        // PROCESAMIENTO SEGÚN EL MÉTODO DE PAGO
        if ($request->payment_method == 'breb') {
            $order_data['payment_method']    = 'breb';
            $order_data['payment_status']    = 'unpaid';
            $order_data['payment_reference'] = $request->payment_reference;
        } else {
            $order_data['payment_method']    = 'cod';
            $order_data['payment_status']    = 'unpaid';
            $order_data['payment_reference'] = null;
        }

        $order->fill($order_data);
        $status = $order->save();

        if ($status) {
            $users = User::where('role', 'admin')->first();
            $mensajeNotificacion = ($request->payment_method == 'breb') 
                ? 'Nueva orden registrada con transferencia Bre-B' 
                : 'Nueva orden registrada con Pago Contra Entrega';

            $details = [
                'title'     => $mensajeNotificacion,
                'actionURL' => route('order.show', $order->id),
                'fas'       => 'fa-file-alt'
            ];

            if ($users) {
                Notification::send($users, new StatusNotification($details));
            }

            session()->forget('cart');
            session()->forget('coupon');
            Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => $order->id]);

            $msgSuccess = ($request->payment_method == 'breb') 
                ? 'Tu pedido se ha registrado. Verificaremos tu transferencia para procesarlo.' 
                : 'Tu pedido ha sido realizado con éxito.';

            request()->session()->flash('success', $msgSuccess);
            return redirect()->route('home');
        } else {
            request()->session()->flash('error', 'Ocurrió un error al procesar tu pedido. Inténtalo de nuevo.');
            return back();
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
            'status'         => 'required|in:new,process,delivered,cancel',
            'payment_status' => 'required|in:paid,unpaid',
        ]);
        
        $data = $request->all();
    
        if ($request->status == 'delivered' && $order->status != 'delivered') {
            foreach ($order->cart as $cart) {
                $product        = $cart->product;
                $product->stock -= $cart->quantity;
                $product->save();
            }
        }
    
        $status = $order->fill($data)->save();
        
        if ($status) {
            request()->session()->flash('success', 'Pedido actualizado correctamente');
        } else {
            request()->session()->flash('error', 'Error al actualizar el pedido');
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
        $file_name = $order->order_number . '-' . $order->first_name . '.pdf';
        $pdf = PDF::loadview('backend.order.pdf', compact('order'));
        return $pdf->download($file_name);
    }

    // Income chart
    public function incomeChart(Request $request)
    {
        $year = \Carbon\Carbon::now()->year;
        $items = Order::with(['cart_info'])->whereYear('created_at', $year)->where('status', 'delivered')->get()
            ->groupBy(function ($d) {
                return \Carbon\Carbon::parse($d->created_at)->format('m');
            });

        $result = [];
        foreach ($items as $month => $item_collections) {
            foreach ($item_collections as $item) {
                $amount = $item->cart_info->sum('amount');
                $m = intval($month);
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