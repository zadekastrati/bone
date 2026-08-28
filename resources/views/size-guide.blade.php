@extends('layouts.app')

@section('title', __('Size Guide'))

@section('content')
    <div class="mx-auto max-w-4xl py-12">
        <h1 class="heading-page mb-8 text-center" id="size-guide-heading">{{ __('Size Guide') }}</h1>

        <div class="panel p-8 sm:p-12" aria-labelledby="size-guide-heading">
            <p class="text-sm text-ink-600">
                {{ __('Use this quick guide to choose your fit. If you are between sizes, size up for comfort or size down for a more compressive fit.') }}
            </p>

            <div class="mt-8 overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Size') }}</th>
                            <th>{{ __('Bust (cm)') }}</th>
                            <th>{{ __('Waist (cm)') }}</th>
                            <th>{{ __('Hips (cm)') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-semibold text-ink-900">XS</td>
                            <td>79-84</td>
                            <td>61-66</td>
                            <td>86-91</td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-ink-900">S</td>
                            <td>84-89</td>
                            <td>66-71</td>
                            <td>91-97</td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-ink-900">M</td>
                            <td>89-94</td>
                            <td>71-76</td>
                            <td>97-102</td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-ink-900">L</td>
                            <td>94-102</td>
                            <td>76-84</td>
                            <td>102-109</td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-ink-900">XL</td>
                            <td>102-109</td>
                            <td>84-91</td>
                            <td>109-117</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="mt-6 text-sm text-ink-600">
                {!! __('Need help finding your size? :link and share your usual brand and size. We\'ll help you find the right fit.', ['link' => '<a href="'.route('contact').'" class="font-medium text-accent-700 hover:text-accent-600">'.__('Contact us').'</a>']) !!}
            </p>
        </div>
    </div>
@endsection
