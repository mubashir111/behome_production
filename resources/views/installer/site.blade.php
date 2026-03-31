@extends('installer.layouts.master')

@section('template_title')
    {{ trans('installer.site.templateTitle') }}
@endsection

@section('title')
    {{ trans('installer.site.title') }}
@endsection

@section('container')
    <ul class="installer-track">
        <li onclick="handleLinkForInstaller('{{ route('installer.index') }}')" class="done">
            <i class="fa-solid fa-house"></i>
        </li>
        <li onclick="handleLinkForInstaller('{{ route('installer.requirement') }}')" class="done">
            <i class="fa-solid fa-server"></i>
        </li>
        <li onclick="handleLinkForInstaller('{{ route('installer.permission') }}')" class="done">
            <i class="fa-sharp fa-solid fa-unlock"></i>
        </li>
        <li onclick="handleLinkForInstaller('{{ route('installer.license') }}')" class="done">
            <i class="fa-solid fa-key"></i>
        </li>
        <li class="active"><i class="fa-solid fa-gear"></i></li>
        <li><i class="fa-solid fa-database"></i></li>
        <li><i class="fa-solid fa-unlock-keyhole"></i></li>
    </ul>

    <span class="my-6 w-full h-[1px] bg-[#EFF0F6]"></span>

    @if($errors->has('global'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-5 rounded relative" role="alert">
            <span class="block sm:inline text-[#E93C3C]">{{ $errors->first('global') }}</span>
        </div>
    @endif

    <form class="w-full" method="post" action="{{ route('installer.siteStore') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        {{-- ── Application ── --}}
        <div class="mb-4">
            <label class="text-sm font-medium block mb-1.5 text-heading">
                {{ trans('installer.site.label.app_name') }} <span class="text-[#E93C3C]">*</span>
            </label>
            <input name="app_name" type="text" value="{{ old('app_name') }}"
                   placeholder="Behome"
                   class="w-full h-12 rounded-lg px-4 border border-[#D9DBE9]">
            @if ($errors->has('app_name'))
                <small class="block mt-2 text-sm font-medium text-[#E93C3C]">{{ $errors->first('app_name') }}</small>
            @endif
        </div>

        <div class="mb-4">
            <label class="text-sm font-medium block mb-1.5 text-heading">
                {{ trans('installer.site.label.app_url') }} <span class="text-[#E93C3C]">*</span>
            </label>
            <input name="app_url" type="text" value="{{ old('app_url', 'https://') }}"
                   placeholder="https://api.yourdomain.com"
                   class="w-full h-12 rounded-lg px-4 border border-[#D9DBE9]">
            @if ($errors->has('app_url'))
                <small class="block mt-2 text-sm font-medium text-[#E93C3C]">{{ $errors->first('app_url') }}</small>
            @endif
        </div>

        <div class="mb-6">
            <label class="text-sm font-medium block mb-1.5 text-heading">
                {{ trans('installer.site.label.frontend_url') }} <span class="text-[#E93C3C]">*</span>
            </label>
            <input name="frontend_url" type="text" value="{{ old('frontend_url', 'https://') }}"
                   placeholder="https://yourdomain.com"
                   class="w-full h-12 rounded-lg px-4 border border-[#D9DBE9]">
            <small class="block mt-1 text-xs text-gray-400">The URL where your Next.js frontend is served</small>
            @if ($errors->has('frontend_url'))
                <small class="block mt-2 text-sm font-medium text-[#E93C3C]">{{ $errors->first('frontend_url') }}</small>
            @endif
        </div>

        {{-- ── Mail ── --}}
        <p class="text-sm font-semibold text-heading mb-4 pb-2 border-b border-[#EFF0F6]">
            {{ trans('installer.site.label.mail_section') }}
        </p>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-sm font-medium block mb-1.5 text-heading">
                    {{ trans('installer.site.label.mail_host') }} <span class="text-[#E93C3C]">*</span>
                </label>
                <input name="mail_host" type="text" value="{{ old('mail_host', 'smtp.mailgun.org') }}"
                       class="w-full h-12 rounded-lg px-4 border border-[#D9DBE9]">
                @if ($errors->has('mail_host'))
                    <small class="block mt-2 text-sm font-medium text-[#E93C3C]">{{ $errors->first('mail_host') }}</small>
                @endif
            </div>
            <div>
                <label class="text-sm font-medium block mb-1.5 text-heading">
                    {{ trans('installer.site.label.mail_port') }} <span class="text-[#E93C3C]">*</span>
                </label>
                <input name="mail_port" type="text" value="{{ old('mail_port', '587') }}"
                       class="w-full h-12 rounded-lg px-4 border border-[#D9DBE9]">
                @if ($errors->has('mail_port'))
                    <small class="block mt-2 text-sm font-medium text-[#E93C3C]">{{ $errors->first('mail_port') }}</small>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-sm font-medium block mb-1.5 text-heading">
                    {{ trans('installer.site.label.mail_username') }}
                </label>
                <input name="mail_username" type="text" value="{{ old('mail_username') }}"
                       class="w-full h-12 rounded-lg px-4 border border-[#D9DBE9]">
                @if ($errors->has('mail_username'))
                    <small class="block mt-2 text-sm font-medium text-[#E93C3C]">{{ $errors->first('mail_username') }}</small>
                @endif
            </div>
            <div>
                <label class="text-sm font-medium block mb-1.5 text-heading">
                    {{ trans('installer.site.label.mail_password') }}
                </label>
                <input name="mail_password" type="password" value="{{ old('mail_password') }}"
                       class="w-full h-12 rounded-lg px-4 border border-[#D9DBE9]">
                @if ($errors->has('mail_password'))
                    <small class="block mt-2 text-sm font-medium text-[#E93C3C]">{{ $errors->first('mail_password') }}</small>
                @endif
            </div>
        </div>

        <div class="mb-4">
            <label class="text-sm font-medium block mb-1.5 text-heading">
                {{ trans('installer.site.label.mail_encryption') }}
            </label>
            <select name="mail_encryption" class="w-full h-12 rounded-lg px-4 border border-[#D9DBE9] bg-white">
                <option value="tls"      {{ old('mail_encryption', 'tls') === 'tls'      ? 'selected' : '' }}>TLS (port 587)</option>
                <option value="ssl"      {{ old('mail_encryption') === 'ssl'              ? 'selected' : '' }}>SSL (port 465)</option>
                <option value="starttls" {{ old('mail_encryption') === 'starttls'         ? 'selected' : '' }}>STARTTLS</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-8">
            <div>
                <label class="text-sm font-medium block mb-1.5 text-heading">
                    {{ trans('installer.site.label.mail_from_address') }} <span class="text-[#E93C3C]">*</span>
                </label>
                <input name="mail_from_address" type="email" value="{{ old('mail_from_address') }}"
                       placeholder="noreply@yourdomain.com"
                       class="w-full h-12 rounded-lg px-4 border border-[#D9DBE9]">
                @if ($errors->has('mail_from_address'))
                    <small class="block mt-2 text-sm font-medium text-[#E93C3C]">{{ $errors->first('mail_from_address') }}</small>
                @endif
            </div>
            <div>
                <label class="text-sm font-medium block mb-1.5 text-heading">
                    {{ trans('installer.site.label.mail_from_name') }} <span class="text-[#E93C3C]">*</span>
                </label>
                <input name="mail_from_name" type="text" value="{{ old('mail_from_name') }}"
                       placeholder="Behome"
                       class="w-full h-12 rounded-lg px-4 border border-[#D9DBE9]">
                @if ($errors->has('mail_from_name'))
                    <small class="block mt-2 text-sm font-medium text-[#E93C3C]">{{ $errors->first('mail_from_name') }}</small>
                @endif
            </div>
        </div>

        <button type="submit" class="w-fit mx-auto p-3 px-6 rounded-lg flex items-center justify-center gap-3 bg-primary text-white">
            <span class="text-sm font-medium capitalize">{{ trans('installer.site.next') }}</span>
            <i class="fa-solid fa-angle-right text-sm"></i>
        </button>
    </form>
@endsection
