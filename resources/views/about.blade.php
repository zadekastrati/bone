@extends('layouts.app')

@section('title', __('About Us'))

@section('content')
    <div class="mx-auto max-w-3xl py-12">
        <h1 class="heading-page mb-8 text-center" id="about-heading">{{ __('About Us') }}</h1>

        <div class="panel p-8 sm:p-12 relative" aria-labelledby="about-heading">
            <p class="text-lg leading-relaxed text-ink-800 font-medium mb-6">
                {{ __('BONÉ was founded with a single mission: to create performance activewear designed by an active woman, for active women who train hard and never stop chasing the next challenge.') }}
            </p>
            <p class="mb-4 text-muted">
                {{ __(' Every cut, fabric, and finish is engineered to move with you , through the weight room, the hiking trail, the tennis court, the yoga mat, the pilates reformer, and everywhere in between. Sweat-wicking, breathable, and built to hold its shape no matter how you move. But it does not stop there.From your hardest training day to the walk home, to coffee with a friend, BONÉ moves with your whole day, not just your workout.') }}
            </p>
            <p class="text-muted">
                {{ __('Designed in Europe, made for the way real women move ,strong, soft, and everything in between.') }}
            </p>
        </div>
    </div>
@endsection
