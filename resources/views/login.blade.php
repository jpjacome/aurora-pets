@extends('layouts.public')

@section('title', 'Admin Login')

@push('styles')
    {{-- Using centralized styles in aurora-general.css --}}
@endpush

@section('content')

  @include('partials.header')

  <main class="login-container fade-in">
    <div class="login-card">
    <h1>Login</h1>
    <form method="POST" action="/login">
      @csrf
      <div class="form-row">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" class="form-input" required autofocus>
      </div>
      <div class="form-row">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" class="form-input" required>
      </div>
      <div class="form-row">
        <button type="submit" class="btn btn-primary">Login</button>
      </div>
    </form>
    <div class="login-footer">If you need access, contact the site administrator.</div>
  </main>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.fade-in').forEach(el => el.classList.add('fade-in-1'));
});
</script>
@endpush
