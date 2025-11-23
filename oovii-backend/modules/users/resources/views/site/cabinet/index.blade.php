@extends('cms-users::site.layouts.cabinet')

@php
    /**
     * @var $user \WezomCms\Users\Models\User
     */
@endphp

@section('content')
    <div class="container">
        <h1>{{ SEO::getH1() }}</h1>
        <div>
            @widget('cabinet-menu')
        </div>
        <div class="grid">
            <br>
            <div class="gcell">
                <livewire:users.edit-personal-info />
            </div>
            <br>
            <div class="gcell">
                <livewire:users.change-password />
            </div>
            <br>
            {{--<livewire:orders.user-addresses />--}}
            <br>
            <div class="gcell">
                @widget('cabinet-socials')
            </div>
        </div>
    </div>
@endsection
