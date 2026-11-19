<?php

namespace App\Console\Commands;

use App\Modules\Identity\Services\OtpService;
use App\Modules\Notifications\Services\MeliPayamakService;
use Illuminate\Console\Command;
use Throwable;

class MeliPayamakTestCommand extends Command
{
    protected $signature = 'melipayamak:test
                            {mobile : شماره موبایل مقصد مثل 09123456789}
                            {--force : ارسال واقعی حتی در صورت هشدار (نیاز به الگوی تأییدشده)}';

    protected $description = 'بررسی پیکربندی ملی‌پیامک و ارسال آزمایشی OTP الگویی';

    public function handle(MeliPayamakService $meli, OtpService $otp): int
    {
        $mobile = $otp->normalizeMobile((string) $this->argument('mobile'));

        $this->info('=== تشخیص ملی‌پیامک / آله ===');
        $diagnosis = $meli->diagnose();

        foreach ($diagnosis as $key => $value) {
            $display = is_bool($value) ? ($value ? 'yes' : 'no') : ($value ?? '—');
            $this->line(str_pad($key, 28).': '.$display);
        }

        if (! preg_match('/^09\d{9}$/', $mobile)) {
            $this->error('شماره موبایل نامعتبر است.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('موبایل مقصد (ماسک‌شده): '.$meli->maskMobile($mobile));

        if (! $diagnosis['sdk_installed']) {
            $this->error('SDK ملی‌پیامک نصب نیست.');

            return self::FAILURE;
        }

        if (! $diagnosis['credentials_configured']) {
            $this->error('MELIPAYAMAK_USERNAME یا MELIPAYAMAK_API_KEY خالی است.');

            return self::FAILURE;
        }

        if (! $diagnosis['pattern_code']) {
            $this->error('MELIPAYAMAK_PATTERN_CODE خالی است.');

            return self::FAILURE;
        }

        if ($diagnosis['pattern_variable_count'] > 1 && ! $diagnosis['soap_loaded']) {
            $this->error('برای الگوی چندمتغیره، extension=soap را در php.ini فعال کنید و PHP را restart کنید.');
            $this->line('مسیر فعلی php.ini را با دستور php --ini ببینید.');

            return self::FAILURE;
        }

        if (! $diagnosis['pattern_approved']) {
            $this->warn('MELIPAYAMAK_PATTERN_APPROVED=false — ارسال واقعی مسدود است.');
            $this->line('پس از تأیید الگو در پنل ملی‌پیامک، این مقدار را true کنید.');

            if (! $this->option('force') && ! $diagnosis['test_mode']) {
                return self::FAILURE;
            }

            if ($diagnosis['test_mode']) {
                $this->warn('حالت آزمایشی: فقط شبیه‌سازی ارسال انجام می‌شود.');
            }
        }

        if ($diagnosis['pattern_approved'] && ! $this->confirm('ارسال واقعی OTP به '.$meli->maskMobile($mobile).' انجام شود؟', false)) {
            $this->info('لغو شد.');

            return self::SUCCESS;
        }

        try {
            $code = $otp->generateSecureOtp((int) config('services.melipayamak.otp_length', 5));
            $result = $meli->sendOtpPattern($mobile, $code);

            $this->newLine();
            $this->info($result->simulated ? 'نتیجه: شبیه‌سازی‌شده (بدون ارسال واقعی)' : 'نتیجه: درخواست به ارائه‌دهنده ارسال شد');
            $this->line('پیام: '.$result->message);
            $this->line('موفق: '.($result->success ? 'yes' : 'no'));

            if ($result->providerResponse !== null) {
                $safe = is_scalar($result->providerResponse)
                    ? (string) $result->providerResponse
                    : json_encode($result->providerResponse, JSON_UNESCAPED_UNICODE);
                $this->line('پاسخ ارائه‌دهنده: '.$safe);
            }

            if ($result->simulated && $this->getLaravel()->environment('local') && config('app.debug')) {
                $this->warn('کد آزمایشی فقط در محیط debug محلی نمایش داده می‌شود: '.$code);
            }

            return $result->success ? self::SUCCESS : self::FAILURE;
        } catch (Throwable $e) {
            $this->error('خطا: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
