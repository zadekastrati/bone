@extends('layouts.app')

@section('title', 'Size Guide')

@section('content')
    <div class="mx-auto max-w-4xl py-12">
        <h1 class="heading-page mb-8 text-center" id="size-guide-heading">Size Guide</h1>

        <div class="panel p-8 sm:p-12" aria-labelledby="size-guide-heading">
            <p class="text-sm text-ink-600">
                Use this quick guide to choose your fit. If you are between sizes, size up for comfort or size down for a more compressive fit.
            </p>

            <div class="mt-8 overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Size</th>
                            <th>Bust (in)</th>
                            <th>Waist (in)</th>
                            <th>Hips (in)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-semibold text-ink-900">XS</td>
                            <td>31-33</td>
                            <td>24-26</td>
                            <td>34-36</td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-ink-900">S</td>
                            <td>33-35</td>
                            <td>26-28</td>
                            <td>36-38</td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-ink-900">M</td>
                            <td>35-37</td>
                            <td>28-30</td>
                            <td>38-40</td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-ink-900">L</td>
                            <td>37-40</td>
                            <td>30-33</td>
                            <td>40-43</td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-ink-900">XL</td>
                            <td>40-43</td>
                            <td>33-36</td>
                            <td>43-46</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="mt-6 text-sm text-ink-600">
                Need help choosing? Reach out from our
                <a href="{{ route('contact') }}" class="font-medium text-accent-700 hover:text-accent-600">Contact Us</a>
                page and include your usual brand/size for recommendations.
            </p>
        </div>
    </div>
@endsection
