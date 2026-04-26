@extends('layouts.app')
@section('title', 'Settings')

@push('head')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="mb-6">
    <p class="text-xs font-semibold tracking-widest text-[var(--muted)] uppercase">Administration</p>
    <h1 class="font-serif text-4xl mt-1">Settings</h1>
</div>

<div x-data="{ tab: '{{ request('tab', 'general') }}' }">
    {{-- Tab nav --}}
    <div class="flex gap-1 mb-6 border-b border-[var(--line)] pb-px">
        <button @click="tab='general'" :class="tab==='general' ? 'border-b-2 border-[var(--ink)] text-[var(--ink)]' : 'text-[var(--muted)]'" class="px-4 py-2.5 text-sm font-medium transition-colors -mb-px">General</button>
        <button @click="tab='sms'" :class="tab==='sms' ? 'border-b-2 border-[var(--ink)] text-[var(--ink)]' : 'text-[var(--muted)]'" class="px-4 py-2.5 text-sm font-medium transition-colors -mb-px">SMS Gateway</button>
        <button @click="tab='backup'" :class="tab==='backup' ? 'border-b-2 border-[var(--ink)] text-[var(--ink)]' : 'text-[var(--muted)]'" class="px-4 py-2.5 text-sm font-medium transition-colors -mb-px">Database Backup</button>
    </div>

    {{-- ═══════════════ GENERAL TAB ═══════════════ --}}
    <div x-show="tab==='general'" x-cloak>
        <form method="POST" action="{{ route('settings.general') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="grid md:grid-cols-3 gap-6">
                {{-- Branding --}}
                <div class="md:col-span-2 card p-6">
                    <h2 class="font-serif text-xl mb-4">Branding & Contact</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Application Name</label>
                            <input name="app_name" value="{{ old('app_name', $settings['app_name'] ?? config('app.name')) }}" class="input" required>
                        </div>
                        <div>
                            <label class="label">Tagline</label>
                            <input name="app_tagline" value="{{ old('app_tagline', $settings['app_tagline'] ?? '') }}" class="input" placeholder="Property management made easy">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label">Address</label>
                            <input name="app_address" value="{{ old('app_address', $settings['app_address'] ?? '') }}" class="input" placeholder="Office address">
                        </div>
                        <div>
                            <label class="label">Phone</label>
                            <input name="app_phone" value="{{ old('app_phone', $settings['app_phone'] ?? '') }}" class="input" placeholder="+880...">
                        </div>
                        <div>
                            <label class="label">Email</label>
                            <input name="app_email" type="email" value="{{ old('app_email', $settings['app_email'] ?? '') }}" class="input" placeholder="admin@company.com">
                        </div>
                    </div>
                </div>

                {{-- Logo --}}
                <div class="card p-6 flex flex-col items-center justify-center" x-data="{ preview: '{{ $settings['logo_path'] ? asset('storage/'.$settings['logo_path']) : '' }}' }">
                    <h2 class="font-serif text-xl mb-3 self-start">Logo</h2>
                    <div class="w-28 h-28 rounded-2xl border-2 border-dashed border-[var(--line)] flex items-center justify-center overflow-hidden bg-[var(--bg)] mb-3">
                        <template x-if="preview">
                            <img :src="preview" class="w-full h-full object-contain">
                        </template>
                        <template x-if="!preview">
                            <span class="text-[var(--muted)] text-xs text-center px-2">No logo</span>
                        </template>
                    </div>
                    <input type="file" name="logo" accept="image/*" class="text-xs w-full" @change="preview = URL.createObjectURL($event.target.files[0])">
                    <p class="text-[0.7rem] text-[var(--muted)] mt-1">PNG, JPG, SVG. Max 2MB.</p>
                </div>
            </div>

            {{-- Formatting & billing --}}
            <div class="card p-6 mt-6">
                <h2 class="font-serif text-xl mb-4">Formatting & Billing</h2>
                <div class="grid sm:grid-cols-4 gap-4">
                    <div>
                        <label class="label">Currency Symbol</label>
                        <input name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '৳') }}" class="input" required>
                    </div>
                    <div>
                        <label class="label">Date Format</label>
                        <select name="date_format" class="select">
                            @foreach(['d M Y','d/m/Y','m/d/Y','Y-m-d','d-m-Y'] as $fmt)
                                <option value="{{ $fmt }}" {{ ($settings['date_format'] ?? 'd M Y') === $fmt ? 'selected' : '' }}>{{ now()->format($fmt) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Late Fee %</label>
                        <input name="late_fee_percent" type="number" step="0.1" min="0" max="100" value="{{ old('late_fee_percent', $settings['late_fee_percent'] ?? 0) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Grace Period (days)</label>
                        <input name="grace_period_days" type="number" min="0" max="60" value="{{ old('grace_period_days', $settings['grace_period_days'] ?? 0) }}" class="input">
                    </div>
                </div>
            </div>

            <div class="mt-5"><button type="submit" class="btn btn-primary">Save General Settings</button></div>
        </form>
    </div>

    {{-- ═══════════════ SMS TAB ═══════════════ --}}
    <div x-show="tab==='sms'" x-cloak>
        <div class="grid lg:grid-cols-5 gap-6">
            {{-- Config form --}}
            <div class="lg:col-span-3">
                <form method="POST" action="{{ route('settings.sms') }}">
                    @csrf @method('PUT')
                    <div class="card p-6" x-data="{ provider: '{{ $settings['sms_provider'] ?? 'none' }}' }">
                        <h2 class="font-serif text-xl mb-4">SMS Provider</h2>

                        <div class="mb-4">
                            <label class="label">Provider</label>
                            <select name="sms_provider" x-model="provider" class="select">
                                <option value="none">— Disabled —</option>
                                <option value="twilio">Twilio</option>
                                <option value="msg91">MSG91</option>
                                <option value="ssl_wireless">SSL Wireless</option>
                                <option value="custom_http">Custom HTTP API</option>
                            </select>
                        </div>

                        {{-- Twilio --}}
                        <div x-show="provider==='twilio'" x-cloak class="space-y-3 p-4 rounded-xl bg-[var(--bg)] mb-4">
                            <p class="text-xs font-semibold text-[var(--muted)] uppercase tracking-wider">Twilio Credentials</p>
                            <div><label class="label">Account SID</label><input name="sms_twilio_sid" value="{{ $settings['sms_twilio_sid'] ?? '' }}" class="input"></div>
                            <div><label class="label">Auth Token</label><input name="sms_twilio_token" value="{{ $settings['sms_twilio_token'] ?? '' }}" class="input" type="password"></div>
                            <div><label class="label">From Number</label><input name="sms_twilio_from" value="{{ $settings['sms_twilio_from'] ?? '' }}" class="input" placeholder="+1..."></div>
                        </div>

                        {{-- MSG91 --}}
                        <div x-show="provider==='msg91'" x-cloak class="space-y-3 p-4 rounded-xl bg-[var(--bg)] mb-4">
                            <p class="text-xs font-semibold text-[var(--muted)] uppercase tracking-wider">MSG91 Credentials</p>
                            <div><label class="label">Auth Key</label><input name="sms_msg91_auth_key" value="{{ $settings['sms_msg91_auth_key'] ?? '' }}" class="input"></div>
                            <div><label class="label">Sender ID</label><input name="sms_msg91_sender_id" value="{{ $settings['sms_msg91_sender_id'] ?? '' }}" class="input"></div>
                            <div><label class="label">Template ID</label><input name="sms_msg91_template_id" value="{{ $settings['sms_msg91_template_id'] ?? '' }}" class="input"></div>
                        </div>

                        {{-- SSL Wireless --}}
                        <div x-show="provider==='ssl_wireless'" x-cloak class="space-y-3 p-4 rounded-xl bg-[var(--bg)] mb-4">
                            <p class="text-xs font-semibold text-[var(--muted)] uppercase tracking-wider">SSL Wireless Credentials</p>
                            <div><label class="label">API Token</label><input name="sms_ssl_api_token" value="{{ $settings['sms_ssl_api_token'] ?? '' }}" class="input"></div>
                            <div><label class="label">SID (Sender ID)</label><input name="sms_ssl_sid" value="{{ $settings['sms_ssl_sid'] ?? '' }}" class="input"></div>
                        </div>

                        {{-- Custom HTTP --}}
                        <div x-show="provider==='custom_http'" x-cloak class="space-y-3 p-4 rounded-xl bg-[var(--bg)] mb-4">
                            <p class="text-xs font-semibold text-[var(--muted)] uppercase tracking-wider">Custom HTTP API</p>
                            <div><label class="label">API URL</label><input name="sms_custom_url" value="{{ $settings['sms_custom_url'] ?? '' }}" class="input" placeholder="https://api.example.com/sms"></div>
                            <div class="grid grid-cols-3 gap-3">
                                <div><label class="label">Method</label>
                                    <select name="sms_custom_method" class="select">
                                        <option value="POST" {{ ($settings['sms_custom_method'] ?? 'POST')==='POST' ? 'selected' : '' }}>POST</option>
                                        <option value="GET" {{ ($settings['sms_custom_method'] ?? '')==='GET' ? 'selected' : '' }}>GET</option>
                                    </select>
                                </div>
                                <div><label class="label">Phone Param</label><input name="sms_custom_phone_param" value="{{ $settings['sms_custom_phone_param'] ?? 'phone' }}" class="input"></div>
                                <div><label class="label">Message Param</label><input name="sms_custom_message_param" value="{{ $settings['sms_custom_message_param'] ?? 'message' }}" class="input"></div>
                            </div>
                            <div><label class="label">Extra Params (JSON)</label><textarea name="sms_custom_extra_params" class="textarea" rows="2" placeholder='{"api_key":"xxx"}'>{{ $settings['sms_custom_extra_params'] ?? '' }}</textarea></div>
                        </div>

                        {{-- Template --}}
                        <div class="mt-4">
                            <label class="label">Reminder Message Template</label>
                            <textarea name="sms_template" class="textarea" rows="3">{{ $settings['sms_template'] ?? 'Dear {tenant}, your rent of {amount} for {unit} ({building}) is due on {due_date}. Please pay soon.' }}</textarea>
                            <p class="text-[0.7rem] text-[var(--muted)] mt-1">Placeholders: <code class="bg-[var(--bg)] px-1 rounded">{tenant}</code> <code class="bg-[var(--bg)] px-1 rounded">{amount}</code> <code class="bg-[var(--bg)] px-1 rounded">{unit}</code> <code class="bg-[var(--bg)] px-1 rounded">{building}</code> <code class="bg-[var(--bg)] px-1 rounded">{due_date}</code> <code class="bg-[var(--bg)] px-1 rounded">{invoice_no}</code></p>
                        </div>

                        <div class="mt-3">
                            <label class="label">Reminder Days Before Due</label>
                            <input name="sms_reminder_days" type="number" min="0" max="30" value="{{ $settings['sms_reminder_days'] ?? 3 }}" class="input w-32">
                        </div>

                        <div class="mt-5"><button type="submit" class="btn btn-primary">Save SMS Settings</button></div>
                    </div>
                </form>

                {{-- Test SMS --}}
                <div class="card p-6 mt-5">
                    <h2 class="font-serif text-xl mb-3">Send Test SMS</h2>
                    <form method="POST" action="{{ route('settings.sms.test') }}" class="flex flex-col sm:flex-row gap-3">
                        @csrf
                        <input name="test_phone" class="input sm:w-48" placeholder="+880..." required>
                        <input name="test_message" class="input flex-1" placeholder="Test message..." value="Hello from BMS!" required>
                        <button class="btn btn-ghost shrink-0">Send Test</button>
                    </form>
                </div>

                {{-- Trigger reminders --}}
                <div class="card p-5 mt-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-semibold">Send Rent Reminders</h3>
                        <p class="text-xs text-[var(--muted)]">SMS all tenants with unpaid/partial invoices due within the configured period.</p>
                    </div>
                    <form method="POST" action="{{ route('settings.sms.reminders') }}" onsubmit="return confirm('Send reminders to all tenants with due invoices?')">
                        @csrf
                        <button class="btn btn-accent shrink-0">Send Reminders Now</button>
                    </form>
                </div>
            </div>

            {{-- SMS Log --}}
            <div class="lg:col-span-2">
                <div class="card p-5">
                    <h2 class="font-serif text-xl mb-3">SMS Log <span class="text-xs font-normal text-[var(--muted)]">(last 50)</span></h2>
                    <div class="space-y-2 max-h-[600px] overflow-y-auto">
                        @forelse($smsLogs as $log)
                            <div class="p-3 rounded-lg bg-[var(--bg)] text-sm">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-medium">{{ $log->phone }}</span>
                                    <span class="badge {{ $log->status === 'sent' ? 'badge-green' : ($log->status === 'failed' ? 'badge-red' : 'badge-amber') }}">{{ $log->status }}</span>
                                </div>
                                <p class="text-xs text-[var(--muted)] line-clamp-2">{{ $log->message }}</p>
                                <p class="text-[0.65rem] text-[var(--muted)] mt-1">{{ $log->created_at->diffForHumans() }} · {{ $log->provider }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-[var(--muted)] text-center py-8">No SMS sent yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════ BACKUP TAB ═══════════════ --}}
    <div x-show="tab==='backup'" x-cloak>
        <div class="grid md:grid-cols-2 gap-6">
            {{-- Create --}}
            <div class="card p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-11 h-11 rounded-xl bg-[var(--ink)] text-white flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0 0l-4-4m4 4l4-4"/></svg>
                    </div>
                    <div>
                        <h2 class="font-serif text-xl leading-tight">Create Backup</h2>
                        <p class="text-xs text-[var(--muted)]">Export full MySQL database as compressed .sql.gz</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('settings.backup.create') }}">
                    @csrf
                    <button class="btn btn-primary w-full justify-center">Create Backup Now</button>
                </form>
            </div>

            {{-- Restore --}}
            <div class="card p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-11 h-11 rounded-xl bg-[var(--accent)] text-white flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M5 19a9 9 0 0115-6.7M19 5a9 9 0 01-15 6.7"/></svg>
                    </div>
                    <div>
                        <h2 class="font-serif text-xl leading-tight">Restore from File</h2>
                        <p class="text-xs text-[var(--muted)]">Upload .sql or .sql.gz to restore</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('settings.backup.restore') }}" enctype="multipart/form-data" onsubmit="return confirm('⚠️ This will OVERWRITE the current database! Are you sure?')">
                    @csrf
                    <input type="file" name="backup_file" accept=".sql,.gz" class="input mb-3 text-sm" required>
                    <button class="btn btn-danger w-full justify-center">Restore Database</button>
                </form>
            </div>
        </div>

        {{-- Backup list --}}
        <div class="card mt-6">
            <div class="px-6 py-4 border-b border-[var(--line)]">
                <h2 class="font-serif text-xl">Existing Backups</h2>
            </div>
            @if(count($backups) > 0)
                <table class="bms">
                    <thead><tr><th>File</th><th>Size</th><th>Created</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                        @foreach($backups as $bk)
                            <tr>
                                <td class="font-medium text-sm">{{ $bk['name'] }}</td>
                                <td class="text-sm text-[var(--muted)]">{{ $bk['size'] }}</td>
                                <td class="text-sm text-[var(--muted)]">{{ $bk['date'] }}</td>
                                <td class="text-right">
                                    <a href="{{ route('settings.backup.download', $bk['name']) }}" class="btn btn-ghost text-xs py-1">Download</a>
                                    <form method="POST" action="{{ route('settings.backup.delete', $bk['name']) }}" class="inline" onsubmit="return confirm('Delete this backup?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger text-xs py-1">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="px-6 py-8 text-sm text-[var(--muted)] text-center">No backups yet. Create your first backup above.</p>
            @endif
        </div>
    </div>
</div>
@endsection
