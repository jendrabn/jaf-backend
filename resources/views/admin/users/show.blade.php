@extends('layouts.admin')

@section('page_title', 'User Detail')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'User' => route('admin.users.index'),
            'User Detail' => null,
        ],
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header">
                    <div class="card-tools">
                        <a class="btn btn-default"
                           href="{{ route('admin.users.index') }}"><i class="bi bi-arrow-left mr-1"></i>Back to list</a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <td>{{ $user->id }}</td>
                            </tr>

                            <tr>
                                <th>NAME</th>
                                <td>{{ $user->name }}</td>
                            </tr>

                            <tr>
                                <th>EMAIL</th>
                                <td>
                                    {{ $user->email }}
                                    <a class="ml-1 text-muted small icon-btn"
                                       href="mailto:{{ $user->email }}"><i class="bi bi-box-arrow-up-right"></i></a>
                                </td>
                            </tr>

                            <tr>
                                <th>EMAIL VERIFIED AT</th>
                                <td>{{ $user->email_verified_at }}</td>
                            </tr>

                            <tr>
                                <th>ROLE</th>
                                <td>
                                    @foreach ($user->roles as $role)
                                        {{ strtoupper($role->name) }}
                                    @endforeach
                                </td>
                            </tr>

                            <tr>
                                <th>PHONE NUMBER</th>
                                <td>
                                    @if ($user->phone)
                                        {{ $user->phone }}
                                        <a class="ml-1 text-muted small icon-btn"
                                           href="tel:{{ $user->phone }}"><i class="bi bi-box-arrow-up-right"></i></a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th>GENDER</th>
                                <td>{{ $user->sex_label }}</td>
                            </tr>

                            <tr>
                                <th>BIRTH DATE</th>
                                <td>{{ $user->birth_date }}</td>
                            </tr>

                            <tr>
                                <th>ORDERS COUNT</th>
                                <td>{{ $user->orders_count }}</td>
                            </tr>

                            <tr>
                                <th>DATE & TIME CREATED</th>
                                <td>{{ $user->created_at }}</td>
                            </tr>

                            <tr>
                                <th>DATE & TIME UPDATED</th>
                                <td>{{ $user->updated_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
