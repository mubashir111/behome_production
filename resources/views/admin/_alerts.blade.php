@if(session('success'))
<div class="glass border-l-4 border-emerald-500 p-4 mb-4 rounded-2xl">{{ session('success') }}</div>
@endif
@if(session('warning'))
<div class="glass border-l-4 border-amber-500 p-4 mb-4 rounded-2xl">{{ session('warning') }}</div>
@endif
@if(session('error'))
<div class="glass border-l-4 border-rose-500 p-4 mb-4 rounded-2xl">{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="glass border-l-4 border-amber-500 p-4 mb-4 rounded-2xl"><ul class="list-disc pl-5 text-sm text-amber-800">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
