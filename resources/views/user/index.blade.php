@extends('user.layouts.master')

@section('main-content')
    <div class="container-fluid">
        @include('user.layouts.notification')
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        </div>

        <!-- Content Row -->

        <div class="row">
            @php
                $orders = DB::table('orders')
                    ->where('user_id', auth()->user()->id)
                    ->paginate(10);
            @endphp
            <!-- Order -->
            <div class="col-xl-12 col-lg-12">
                <table class="table table-bordered" id="order-dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>S.N.</th>
                            <th>Order No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>S.N.</th>
                            <th>Order No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @if (count($orders) > 0)
                            @foreach ($orders as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->first_name }} {{ $order->last_name }}</td>
                                    <td>{{ $order->email }}</td>
                                    <td>{{ $order->quantity }}</td>
                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        @if ($order->status == 'new')
                                            <span class="badge badge-primary">{{ $order->status }}</span>
                                        @elseif($order->status == 'process')
                                            <span class="badge badge-warning">{{ $order->status }}</span>
                                        @elseif($order->status == 'delivered')
                                            <span class="badge badge-success">{{ $order->status }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ $order->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <!-- Botón Ver Detalle -->
                                        <a href="{{ route('user.order.show', $order->id) }}"
                                            class="btn btn-warning btn-sm float-left mr-1"
                                            style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip"
                                            title="view" data-placement="bottom"><i class="fas fa-eye"></i></a>

                                        <!-- Botón Cancelar (solo si está en estado new) -->
                                        @if ($order->status == 'new')
                                            <form method="POST" action="{{ route('user.order.delete', [$order->id]) }}"
                                                class="float-left">
                                                @csrf
                                                @method('delete')
                                                <button class="btn btn-danger btn-sm cancelBtn"
                                                    data-id="{{ $order->id }}"
                                                    style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip"
                                                    data-placement="bottom" title="Cancelar Pedido"><i
                                                        class="fas fa-ban"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <td colspan="8" class="text-center">
                                <h4 class="my-4">You have no order yet!! Please order some products</h4>
                            </td>
                        @endif
                    </tbody>
                </table>

                {{ $orders->links() }}
            </div>
        </div>

    </div>
@endsection

@push('styles')
    <link href="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script type="text/javascript">
        const url = "{{ route('product.order.income') }}";

        // Set new default font family and font color to mimic Bootstrap's default styling
        Chart.defaults.global.defaultFontFamily = 'Nunito',
            '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#858796';

        function number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(',', '').replace(' ', '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        // Area Chart Example
        var ctx = document.getElementById("myAreaChart");

        if (ctx) {
            axios.get(url)
                .then(function(response) {
                    const data_keys = Object.keys(response.data);
                    const data_values = Object.values(response.data);

                    var myLineChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data_keys,
                            datasets: [{
                                label: "Earnings",
                                lineTension: 0.3,
                                backgroundColor: "rgba(78, 115, 223, 0.05)",
                                borderColor: "rgba(78, 115, 223, 1)",
                                pointRadius: 3,
                                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                                pointBorderColor: "rgba(78, 115, 223, 1)",
                                pointHoverRadius: 3,
                                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                                pointHitRadius: 10,
                                pointBorderWidth: 2,
                                data: data_values,
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    left: 10,
                                    right: 25,
                                    top: 25,
                                    bottom: 0
                                }
                            },
                            scales: {
                                xAxes: [{
                                    time: {
                                        unit: 'date'
                                    },
                                    gridLines: {
                                        display: false,
                                        drawBorder: false
                                    },
                                    ticks: {
                                        maxTicksLimit: 7
                                    }
                                }],
                                yAxes: [{
                                    ticks: {
                                        maxTicksLimit: 5,
                                        padding: 10,
                                        callback: function(value, index, values) {
                                            return '$' + number_format(value);
                                        }
                                    },
                                    gridLines: {
                                        color: "rgb(234, 236, 244)",
                                        zeroLineColor: "rgb(234, 236, 244)",
                                        drawBorder: false,
                                        borderDash: [2],
                                        zeroLineBorderDash: [2]
                                    }
                                }],
                            },
                            legend: {
                                display: false
                            },
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#858796",
                                titleMarginBottom: 10,
                                titleFontColor: '#6e707e',
                                titleFontSize: 14,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                intersect: false,
                                mode: 'index',
                                caretPadding: 10,
                                callbacks: {
                                    label: function(tooltipItem, chart) {
                                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label ||
                                            '';
                                        return datasetLabel + ': $' + number_format(tooltipItem.yLabel);
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(function(error) {
                    console.log(error)
                });
        }
    </script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.cancelBtn').click(function(e) {
                var form = $(this).closest('form');
                e.preventDefault();
                swal({
                        title: "¿Estás seguro?",
                        text: "El pedido pasará a estar cancelado.",
                        icon: "warning",
                        buttons: ["Volver", "Sí, cancelar"],
                        dangerMode: true,
                    })
                    .then((willCancel) => {
                        if (willCancel) {
                            form.submit();
                        }
                    });
            })
        })
    </script>
@endpush
