@extends('layouts.app')

@section('content')
<div class="container">

    <h3 class="mb-4">✏️ Edit User</h3>

    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('users._form', ['user' => $user])

    </form>

</div>
@endsection