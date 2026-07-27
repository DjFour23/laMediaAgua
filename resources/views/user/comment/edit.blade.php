@extends('user.layouts.master')

@section('title', 'Detalle del Comentario')

@section('main-content')
<div class="card shadow mb-4">
  <h5 class="card-header font-weight-bold text-primary">Detalle del Comentario</h5>
  <div class="card-body">
    
    <!-- Autor del comentario -->
    <div class="form-group">
      <label class="font-weight-bold text-gray-800">Publicado por:</label>
      <input type="text" disabled class="form-control bg-light" value="{{ $comment->user_info->name ?? 'Usuario' }}">
    </div>

    <!-- Publicación (Post) a la que pertenece -->
    <div class="form-group">
      <label class="font-weight-bold text-gray-800">Publicación (Post):</label>
      <div>
        @if(isset($comment->post))
          <a href="{{ route('blog.detail', $comment->post->slug) }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-external-link-alt"></i> Ver post: {{ $comment->post->title }}
          </a>
        @else
          <input type="text" disabled class="form-control bg-light" value="Publicación no disponible">
        @endif
      </div>
    </div>

    <!-- Si fue una respuesta a otro comentario -->
    @if(isset($comment->parent_info))
    <div class="form-group bg-light p-3 rounded border-left-primary">
      <label class="font-weight-bold text-secondary">En respuesta al comentario de:</label>
      <p class="mb-1 font-weight-bold text-dark">{{ $comment->parent_info->user_info->name ?? 'Usuario' }}</p>
      <blockquote class="blockquote-footer mb-0">
        {{ $comment->parent_info->comment }}
      </blockquote>
    </div>
    @endif

    <!-- Contenido del Comentario -->
    <div class="form-group">
      <label class="font-weight-bold text-gray-800">Comentario:</label>
      <textarea disabled cols="20" rows="5" class="form-control bg-light" style="resize: none;">{{ $comment->comment }}</textarea>
    </div>

    <!-- Estado de Moderación (Solo Lectura) -->
    <div class="form-group">
      <label class="font-weight-bold text-gray-800">Estado de Moderación:</label>
      <div>
        @if($comment->status == 'active')
          <span class="badge badge-success px-3 py-2">Aprobado / Activo</span>
        @else
          <span class="badge badge-warning px-3 py-2">Pendiente de Revisión</span>
        @endif
      </div>
    </div>

    <!-- Fecha de publicación -->
    <div class="form-group">
      <label class="font-weight-bold text-gray-800">Fecha de Envío:</label>
      <p class="text-muted">{{ $comment->created_at ? $comment->created_at->format('D d M, Y \a \l\a\s g:i a') : 'N/A' }}</p>
    </div>

    <hr>

    <a href="{{ route('user.post-comment.index') }}" class="btn btn-secondary shadow-sm">
      <i class="fas fa-arrow-left"></i> Volver a mis comentarios
    </a>

  </div>
</div>
@endsection

@push('styles')
<style>
    .order-info, .shipping-info {
        background: #ECECEC;
        padding: 20px;
    }
    .order-info h4, .shipping-info h4 {
        text-decoration: underline;
    }
</style>
@endpush