@extends('backend.layouts.master')

@section('title','Order Detail')

@section('main-content')
<div class="card">
  <h5 class="card-header">Order Edit</h5>
  <div class="card-body">
    <form action="{{route('order.update',$order->id)}}" method="POST">
      @csrf
      @method('PATCH')

      <!-- Estado del Pago -->
      <div class="form-group">
        <label for="payment_status">Estado del Pago :</label>
        <select name="payment_status" id="payment_status" class="form-control">
          <option value="unpaid" {{ (($order->payment_status=='unpaid') ? 'selected' : '') }}>Sin Pagar (Unpaid)</option>
          <option value="paid" {{ (($order->payment_status=='paid') ? 'selected' : '') }}>Pagado (Paid)</option>
        </select>
      </div>

      <!-- Estado del Pedido -->
      <div class="form-group">
        <label for="status">Estado del Pedido :</label>
        <select name="status" id="status" class="form-control">
          <option value="new" {{($order->status=='delivered' || $order->status=="process" || $order->status=="cancel") ? 'disabled' : ''}}  {{(($order->status=='new')? 'selected' : '')}}>Nuevo (New)</option>
          <option value="process" {{($order->status=='delivered'|| $order->status=="cancel") ? 'disabled' : ''}}  {{(($order->status=='process')? 'selected' : '')}}>En Proceso (Process)</option>
          <option value="delivered" {{($order->status=="cancel") ? 'disabled' : ''}}  {{(($order->status=='delivered')? 'selected' : '')}}>Entregado (Delivered)</option>
          <option value="cancel" {{($order->status=='delivered') ? 'disabled' : ''}}  {{(($order->status=='cancel')? 'selected' : '')}}>Cancelado (Cancel)</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Actualizar Pedido</button>
    </form>
  </div>
</div>
@endsection

@push('styles')
<style>
    .order-info,.shipping-info{
        background:#ECECEC;
        padding:20px;
    }
    .order-info h4,.shipping-info h4{
        text-decoration: underline;
    }
</style>
@endpush