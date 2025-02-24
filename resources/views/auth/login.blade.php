<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <section class="bg-light vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                    <div class="card border border-light-subtle rounded-3 shadow-sm" data-aos="zoom-in-up">
                        <div class="card-body p-3">
                            <div class="text-center mb-2">
                                <a href="#" class="d-flex justify-content-center">
                                    <img src="{{ asset('images/logo-invengo.png') }}" alt="Logo InvenGo" width="180" height="60" style="padding: 5px">
                                </a>
                            </div>
                            <h2 class="fs-6 fw-normal text-center text-secondary mb-4">Masuk ke akun Anda</h2>
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="row gy-2 overflow-hidden">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            {{-- email --}}
                                            <x-text-input id="email" 
                                                class="form-control" 
                                                type="email" 
                                                name="email" 
                                                :value="old('email')" 
                                                required 
                                                autofocus 
                                                autocomplete="username"
                                                placeholder="name@example.com" />
                                            <x-input-label for="email" :value="__('Email')" class="form-label" />
                                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <x-text-input id="password" 
                                                class="form-control"
                                                type="password"
                                                name="password"
                                                required 
                                                autocomplete="current-password"
                                                placeholder="Password" />
                                            <x-input-label for="password" :value="__('Password')" class="form-label" />
                                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex gap-2 justify-content-between">
                                            <div class="form-check">
                                                {{-- <input class="form-check-input" type="checkbox" value="" name="rememberMe" id="rememberMe">
                                                <label class="form-check-label text-secondary" for="rememberMe">
                                                    Keep me logged in
                                                </label> --}}
                                                <label for="remember_me" class="form-check-label text-secondary">
                                                    <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                                                    <span>{{ __('Ingat Saya') }}</span>
                                                </label>
                                            </div>
                                            {{-- <a href="#!" class="link-primary text-decoration-none">Forgot password?</a> --}}
                                            @if (Route::has('password.request'))
                                                <a class="link-primary text-decoration-none" href="{{ route('password.request') }}">
                                                    {{ __('Lupa Password?') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-grid my-3">
                                            {{-- <button class="btn btn-primary btn-lg" type="submit">Log in</button> --}}
                                            <x-primary-button >
                                                {{ __('Log in') }}
                                            </x-primary-button>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <p class="m-0 text-secondary text-center">Belum punya akun? <a href="{{route('register')}}" class="link-primary text-decoration-none">Register</a></p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Session Status -->

    {{-- 

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form> --}}
</x-guest-layout>
