<x-guest-layout>
    <section class="bg-light vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                    <div class="card border border-light-subtle rounded-3 shadow-sm" data-aos="zoom-in-down">
                        <div class="card-body p-3">
                            <div class="text-center mb-2">
                                <a href="#" class="d-flex justify-content-center">
                                    <img src="{{ asset('images/logo-invengo.png') }}" alt="Logo InvenGo" width="180" height="60" style="padding: 5px">
                                </a>
                            </div>
                            <h2 class="fs-6 fw-normal text-center text-secondary mb-3">Daftar Akun Baru</h2>
                            
                            <form method="POST" action="{{ route('register') }}">
                                @csrf
                                <div class="row gy-2 overflow-hidden">
                                    <!-- Name -->
                                    <div class="col-12">
                                        <div class="form-floating mb-2">
                                            <x-text-input id="name" 
                                                class="form-control" 
                                                type="text" 
                                                name="name" 
                                                :value="old('name')" 
                                                required 
                                                autofocus 
                                                autocomplete="name"
                                                placeholder="Nama Lengkap" />
                                            <x-input-label for="name" :value="__('Nama')" class="form-label" />
                                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                                        </div>
                                    </div>

                                    <!-- Email Address -->
                                    <div class="col-12">
                                        <div class="form-floating mb-2">
                                            <x-text-input id="email" 
                                                class="form-control" 
                                                type="email" 
                                                name="email" 
                                                :value="old('email')" 
                                                required 
                                                autocomplete="username"
                                                placeholder="name@example.com" />
                                            <x-input-label for="email" :value="__('Email')" class="form-label" />
                                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                                        </div>
                                    </div>

                                    <!-- Password -->
                                    <div class="col-12">
                                        <div class="form-floating mb-2">
                                            <x-text-input id="password" 
                                                class="form-control"
                                                type="password"
                                                name="password"
                                                required 
                                                autocomplete="new-password"
                                                placeholder="Password" />
                                            <x-input-label for="password" :value="__('Password')" class="form-label" />
                                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                                        </div>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="col-12">
                                        <div class="form-floating mb-2">
                                            <x-text-input id="password_confirmation" 
                                                class="form-control"
                                                type="password"
                                                name="password_confirmation" 
                                                required 
                                                autocomplete="new-password"
                                                placeholder="Konfirmasi Password" />
                                            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="form-label" />
                                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-grid mb-2">
                                            <x-primary-button>
                                                {{ __('Daftar') }}
                                            </x-primary-button>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <p class="m-0 text-secondary text-center">
                                            Sudah punya akun? 
                                            <a href="{{ route('login') }}" class="link-primary text-decoration-none">
                                                Masuk
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-guest-layout>
