@extends('installer.layouts.master')

@section('template_title')
    {{ trans('installer.license.templateTitle') }}
@endsection

@section('title')
    {{ trans('installer.license.title') }}
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
        <li class="active"><i class="fa-solid fa-key"></i></li>
        <li><i class="fa-solid fa-gear"></i></li>
        <li><i class="fa-solid fa-database"></i></li>
        <li><i class="fa-solid fa-unlock-keyhole"></i></li>
    </ul>

    <span class="my-6 w-full h-[1px] bg-[#EFF0F6]"></span>

    <div class="w-full max-h-48 overflow-y-auto rounded-lg border border-[#D9DBE9] p-4 mb-5 text-sm text-gray-600 leading-relaxed">
        <p class="font-semibold text-heading mb-2">Behome E-Commerce Platform</p>
        <p>By installing this software you agree that you will use it in accordance with applicable laws and regulations. You may not redistribute, resell, or sublicense this software without explicit written permission.</p>
        <p class="mt-2">The software is provided "as is" without warranty of any kind. The authors shall not be liable for any damages arising from the use of this software.</p>
        <p class="mt-2">You are responsible for maintaining the security of your installation, keeping credentials safe, and complying with data protection regulations applicable to your jurisdiction.</p>
    </div>

    @if($errors->has('global'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-5 rounded relative" role="alert">
            <span class="block sm:inline text-[#E93C3C]">{{ $errors->first('global') }}</span>
        </div>
    @endif

    <form method="post" action="{{ route('installer.licenseStore') }}" class="w-full">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="flex items-start gap-3 mb-8">
            <input type="checkbox" name="accept_terms" id="accept_terms" value="1"
                   class="mt-1 h-4 w-4 rounded border-[#D9DBE9] cursor-pointer">
            <label for="accept_terms" class="text-sm text-heading cursor-pointer">
                {{ trans('installer.license.label.accept') }}
            </label>
        </div>
        @if ($errors->has('accept_terms'))
            <small class="block -mt-6 mb-4 text-sm font-medium text-[#E93C3C]">{{ $errors->first('accept_terms') }}</small>
        @endif

        <button type="submit"
                class="w-fit mx-auto p-3 px-6 rounded-lg flex items-center justify-center gap-3 bg-primary text-white">
            <span class="text-sm font-medium capitalize">{{ trans('installer.license.next') }}</span>
            <i class="fa-solid fa-angle-right text-sm"></i>
        </button>
    </form>
@endsection
