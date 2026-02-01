@if (session('success') || session('error'))
<div class="global-alert {{ session('success') ? 'success' : 'error' }}" id="globalAlert">
    <span class="material-symbols-outlined">
        {{ session('success') ? 'check_circle' : 'error' }}
    </span>
    <p>{{ session('success') ?? session('error') }}</p>
</div>
@endif
