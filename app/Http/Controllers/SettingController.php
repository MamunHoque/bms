<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SmsLog;
use App\Services\RentReminderService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /* ------------------------------------------------------------------
     * Show — tabbed settings page
     * ---------------------------------------------------------------- */
    public function index()
    {
        $settings = Setting::getMany([
            'app_name', 'app_tagline', 'app_address', 'app_phone', 'app_email',
            'currency_symbol', 'date_format', 'logo_path',
            'late_fee_percent', 'grace_period_days',
            'sms_provider', 'sms_template', 'sms_reminder_days',
            'sms_twilio_sid', 'sms_twilio_token', 'sms_twilio_from',
            'sms_msg91_auth_key', 'sms_msg91_sender_id', 'sms_msg91_template_id',
            'sms_ssl_api_token', 'sms_ssl_sid',
            'sms_custom_url', 'sms_custom_method', 'sms_custom_phone_param',
            'sms_custom_message_param', 'sms_custom_extra_params',
        ], [
            'app_name'          => config('app.name'),
            'currency_symbol'   => '৳',
            'date_format'       => 'd M Y',
            'late_fee_percent'  => '0',
            'grace_period_days' => '0',
            'sms_provider'      => 'none',
            'sms_reminder_days' => '3',
            'sms_template'      => 'Dear {tenant}, your rent of {amount} for {unit} ({building}) is due on {due_date}. Please pay soon.',
            'sms_custom_method' => 'POST',
            'sms_custom_phone_param'   => 'phone',
            'sms_custom_message_param' => 'message',
        ]);

        // Existing backups
        $backups = $this->listBackups();

        // SMS logs (last 50)
        $smsLogs = SmsLog::with('tenant')->latest()->take(50)->get();

        return view('settings.index', compact('settings', 'backups', 'smsLogs'));
    }

    /* ------------------------------------------------------------------
     * General tab — save
     * ---------------------------------------------------------------- */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'app_name'          => 'required|string|max:255',
            'app_tagline'       => 'nullable|string|max:255',
            'app_address'       => 'nullable|string|max:500',
            'app_phone'         => 'nullable|string|max:50',
            'app_email'         => 'nullable|email|max:255',
            'currency_symbol'   => 'required|string|max:10',
            'date_format'       => 'required|string|max:30',
            'late_fee_percent'  => 'nullable|numeric|min:0|max:100',
            'grace_period_days' => 'nullable|integer|min:0|max:60',
            'logo'              => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        $fields = $request->only([
            'app_name', 'app_tagline', 'app_address', 'app_phone', 'app_email',
            'currency_symbol', 'date_format', 'late_fee_percent', 'grace_period_days',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            $old = Setting::get('logo_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('logo')->store('logo', 'public');
            $fields['logo_path'] = $path;
        }

        Setting::setMany($fields);

        return back()->with('status', 'General settings saved successfully.');
    }

    /* ------------------------------------------------------------------
     * SMS tab — save config
     * ---------------------------------------------------------------- */
    public function updateSms(Request $request)
    {
        $request->validate([
            'sms_provider'      => 'required|string|in:none,twilio,msg91,ssl_wireless,custom_http',
            'sms_template'      => 'nullable|string|max:500',
            'sms_reminder_days' => 'nullable|integer|min:0|max:30',
        ]);

        $fields = $request->only([
            'sms_provider', 'sms_template', 'sms_reminder_days',
            'sms_twilio_sid', 'sms_twilio_token', 'sms_twilio_from',
            'sms_msg91_auth_key', 'sms_msg91_sender_id', 'sms_msg91_template_id',
            'sms_ssl_api_token', 'sms_ssl_sid',
            'sms_custom_url', 'sms_custom_method', 'sms_custom_phone_param',
            'sms_custom_message_param', 'sms_custom_extra_params',
        ]);

        Setting::setMany($fields);

        return back()->with('status', 'SMS settings saved successfully.');
    }

    /* ------------------------------------------------------------------
     * SMS tab — send test
     * ---------------------------------------------------------------- */
    public function testSms(Request $request, SmsService $sms)
    {
        $request->validate([
            'test_phone'   => 'required|string|max:20',
            'test_message' => 'required|string|max:300',
        ]);

        $result = $sms->send($request->test_phone, $request->test_message);

        return back()->with('status', $result['message']);
    }

    /* ------------------------------------------------------------------
     * SMS tab — send rent reminders
     * ---------------------------------------------------------------- */
    public function sendReminders(RentReminderService $reminderService)
    {
        $result = $reminderService->sendReminders();

        $msg = "Reminders sent: {$result['sent']} | Failed: {$result['failed']} | Skipped (no phone): {$result['skipped']}";
        return back()->with('status', $msg);
    }

    /* ------------------------------------------------------------------
     * Backup tab — create
     * ---------------------------------------------------------------- */
    public function createBackup()
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'bms_backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $dir . '/' . $filename;

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $db   = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg((string)$port),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($db),
            escapeshellarg($filepath)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            Log::error('Backup failed', ['output' => implode("\n", $output)]);
            return back()->with('status', 'Backup failed. Check server logs for details.');
        }

        // Compress
        $gzFile = $filepath . '.gz';
        $fp = fopen($filepath, 'rb');
        $gz = gzopen($gzFile, 'wb9');
        while (!feof($fp)) {
            gzwrite($gz, fread($fp, 524288));
        }
        gzclose($gz);
        fclose($fp);
        unlink($filepath);

        return back()->with('status', "Backup created: {$filename}.gz");
    }

    /* ------------------------------------------------------------------
     * Backup tab — download
     * ---------------------------------------------------------------- */
    public function downloadBackup(string $file)
    {
        $path = storage_path('app/backups/' . basename($file));

        if (! file_exists($path)) {
            return back()->with('status', 'Backup file not found.');
        }

        return response()->download($path);
    }

    /* ------------------------------------------------------------------
     * Backup tab — delete
     * ---------------------------------------------------------------- */
    public function deleteBackup(string $file)
    {
        $path = storage_path('app/backups/' . basename($file));

        if (file_exists($path)) {
            unlink($path);
        }

        return back()->with('status', 'Backup deleted.');
    }

    /* ------------------------------------------------------------------
     * Backup tab — restore from upload
     * ---------------------------------------------------------------- */
    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:51200', // 50MB
        ]);

        $uploaded = $request->file('backup_file');
        $ext = strtolower($uploaded->getClientOriginalExtension());

        $tmpPath = $uploaded->storeAs('backups', 'restore_tmp.' . $ext, 'local');
        $fullPath = storage_path('app/private/' . $tmpPath);

        // If gzipped, decompress first
        if ($ext === 'gz') {
            $sqlPath = str_replace('.gz', '', $fullPath);
            $gz = gzopen($fullPath, 'rb');
            $fp = fopen($sqlPath, 'wb');
            while (!gzeof($gz)) {
                fwrite($fp, gzread($gz, 524288));
            }
            gzclose($gz);
            fclose($fp);
            unlink($fullPath);
            $fullPath = $sqlPath;
        }

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $db   = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');

        $cmd = sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s 2>&1',
            escapeshellarg($host),
            escapeshellarg((string)$port),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($db),
            escapeshellarg($fullPath)
        );

        exec($cmd, $output, $returnCode);
        @unlink($fullPath);

        if ($returnCode !== 0) {
            Log::error('Restore failed', ['output' => implode("\n", $output)]);
            return back()->with('status', 'Restore failed. Check server logs.');
        }

        return back()->with('status', 'Database restored successfully from backup.');
    }

    /* ------------------------------------------------------------------
     * Helpers
     * ---------------------------------------------------------------- */
    protected function listBackups(): array
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/bms_backup_*.sql.gz');
        $backups = [];
        foreach ($files as $f) {
            $backups[] = [
                'name'    => basename($f),
                'size'    => $this->humanSize(filesize($f)),
                'date'    => date('d M Y H:i', filemtime($f)),
                'bytes'   => filesize($f),
            ];
        }
        // Most recent first
        usort($backups, fn($a, $b) => $b['bytes'] <=> $a['bytes']);
        return $backups;
    }

    protected function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}
