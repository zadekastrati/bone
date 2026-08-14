@extends('layouts.app')

@section('title', __('About Us'))

@section('content')
    <div class="mx-auto max-w-3xl py-12">
        <h1 class="heading-page mb-8 text-center" id="about-heading">{{ __('About Us') }}</h1>

        <div class="panel p-8 sm:p-12 relative" aria-labelledby="about-heading">
            <p class="text-lg leading-relaxed text-ink-800 font-medium mb-6">
                {{ __('Boné was founded with a single mission: to create performance gym wear designed specifically for women who train hard.') }}
            </p>
            <p class="mb-4 text-muted">
                {{ __('We believe that your gear shouldn\'t be a distraction. Every cut, fabric, and finish is built for the floor, engineered to move with you through every squat, sprint, and stretch. Our materials are sweat-wicking, breathable, and relentlessly durable.') }}
            </p>
            <p class="text-muted">
                {{ __('From our headquarters to your gym bag, we\'re dedicated to empowering your journey with apparel that never quits.') }}
            </p>
        </div>
    </div>
@endsection
