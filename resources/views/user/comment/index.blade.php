@extends('user.layouts.master')
@section('title','Comment Page')
@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('user.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Comment Lists</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($comments)>0)
        <table class="table table-bordered" id="order-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>S.N.</th>
              <th>Author</th>
              <th>Post Title</th>
              <th>Message</th>
              <th>Date</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th>S.N.</th>
              <th>Author</th>
              <th>Post Title</th>
              <th>Message</th>
              <th>Date</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </tfoot>
          <tbody>
            @foreach($comments as $comment)
                <tr>
                    <td>{{$comment->id}}</td>
                    <td>{{$comment->user_info['name'] ?? 'Usuario'}}</td>
                    <td>{{$comment->post->title ?? 'Post no disponible'}}</td>
                    <td>{{$comment->comment}}</td>
                    <td>{{$comment->created_at->format('M d D, Y g: i a')}}</td>
                    <td>
                        @if($comment->status=='active')
                          <span class="badge badge-success">{{$comment->status}}</span>
                        @else
                          <span class="badge badge-warning">{{$comment->status}}</span>
                        @endif
                    </td>
                    <td>
                        <!-- Únicamente Botón Ver Detalle (Solo lectura) -->
                        <a href="{{route('user.post-comment.edit',$comment->id)}}" class="btn btn-warning btn-sm" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Ver Detalle" data-placement="bottom">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
          </tbody>
        </table>
        @else
          <h6 class="text-center">No post comments found!!!</h6>
        @endif
      </div>
    </div>
</div>
@endsection

@push('styles')
  <link href="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
@endpush

@push('scripts')

  <!-- Page level plugins -->
  <script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>

  <!-- Page level custom scripts -->
  <script src="{{asset('backend/js/demo/datatables-demo.js')}}"></script>
  <script>
      $('#order-dataTable').DataTable({
            "columnDefs":[
                {
                    "orderable":false,
                    "targets":[5,6]
                }
            ]
      });
  </script>
@endpush