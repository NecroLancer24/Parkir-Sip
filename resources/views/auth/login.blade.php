<x-layouts.auth
    title="Sign in"
    :headline="['Every space. Every gate.', 'One clear view.']"
>
    <div>
        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-brand">Your parking, connected</p>

        <h1 class="mt-3 text-[30px] font-semibold leading-tight tracking-[-0.02em] text-ink">Welcome back</h1>

        <p class="mt-2 text-[12.5px] text-ink-soft">
            Sign in to keep your parking operations moving.
        </p>
    </div>

    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-[11px] font-medium text-ink-soft">Work email</label>

            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-ink-muted">
                    <x-icon name="mail" class="size-4" />
                </span>

                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="alex@centralpark.co"
                    @class([
                        'h-11 w-full rounded-[10px] border bg-white pl-10 pr-3 text-[12.5px] text-ink placeholder:text-ink-muted transition-colors focus:outline-none focus:ring-2',
                        'border-line focus:border-brand focus:ring-brand/15' => ! $errors->has('email'),
                        'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('email'),
                    ])
                >
            </div>

            @error('email')
                <p class="mt-1.5 text-[10.5px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-[11px] font-medium text-ink-soft">Password</label>

            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-ink-muted">
                    <x-icon name="lock" class="size-4" />
                </span>

                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    @class([
                        'h-11 w-full rounded-[10px] border bg-white pl-10 pr-11 text-[12.5px] text-ink placeholder:text-ink-muted transition-colors focus:outline-none focus:ring-2',
                        'border-line focus:border-brand focus:ring-brand/15' => ! $errors->has('password'),
                        'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('password'),
                    ])
                >

                <button
                    type="button"
                    data-password-toggle="password"
                    class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-ink-muted transition-colors hover:text-ink-soft"
                    aria-label="Show password"
                >
                    <x-icon name="eye" class="size-4" data-eye-open />
                    <x-icon name="eye-off" class="hidden size-4" data-eye-closed />
                </button>
            </div>

            @error('password')
                <p class="mt-1.5 text-[10.5px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label class="inline-flex cursor-pointer items-center gap-2 text-[11px] text-ink-soft">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    class="size-3.5 rounded border-line accent-brand focus:ring-brand/30"
                    @checked(old('remember'))
                >
                Remember me for 30 days
            </label>

            <a href="#" class="text-[11px] font-medium text-brand transition-colors hover:text-brand-strong">
                Forgot password?
            </a>
        </div>

        <button
            type="submit"
            class="h-11 w-full rounded-[10px] bg-brand text-[12.5px] font-semibold text-white transition-colors hover:bg-brand-strong focus:outline-none focus:ring-2 focus:ring-brand/25 focus:ring-offset-2"
        >
            Sign in
        </button>
    </form>

    <div class="my-6 flex items-center gap-3">
        <span class="h-px flex-1 bg-line-soft"></span>
        <span class="text-[9.5px] font-medium uppercase tracking-[0.16em] text-ink-muted">or continue with</span>
        <span class="h-px flex-1 bg-line-soft"></span>
    </div>

    <button
        type="button"
        title="Google sign-in is not configured yet"
        class="flex h-11 w-full items-center justify-center gap-2.5 rounded-[10px] border border-line bg-white text-[12px] font-medium text-ink-soft transition-colors hover:bg-subtle"
    >
        <x-icon name="google" class="size-4" />
        Google
    </button>

    <p class="mt-6 text-center text-[11.5px] text-ink-muted">
        New to Parkir Sip?
        <a href="{{ route('register') }}" class="font-semibold text-brand transition-colors hover:text-brand-strong">Create an account</a>
    </p>
</x-layouts.auth>
