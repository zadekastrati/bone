@extends('layouts.app')

@section('title', __('About Us'))

@section('content')
    <div class="mx-auto max-w-3xl py-12">
        <h1 class="heading-page mb-8 text-center" id="about-heading">{{ __('About Us') }}</h1>

        <div class="panel p-8 sm:p-12 relative" aria-labelledby="about-heading">
            <p class="text-lg leading-relaxed text-ink-800 font-medium mb-6">
                {{ __('BONÉ was created with a simple purpose: to make high-quality activewear that empowers every movement. Designed by an active woman for women who move, BONÉ brings together performance, comfort, and confidence for every fitness journey.') }}
            </p>
            <p class="mb-4 text-muted">
                {{ __('From your hardest training session to the walk home or coffee with a friend, BONÉ is designed to move with you. Thoughtful fabrics, considered fits, and versatile designs deliver performance and comfort through every workout and whatever comes next.') }}
            </p>
            <p class="text-muted">
                {{ __('Designed in Europe. Built for movement. Designed for you.') }}
            </p>
        </div>
    </div>
@endsection
