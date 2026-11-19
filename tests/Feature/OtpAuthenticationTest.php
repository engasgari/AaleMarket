<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\OtpCode;
use App\Modules\Identity\Services\OtpService;
use App\Modules\Notifications\DTOs\MeliPayamakSendResult;
use App\Modules\Notifications\Services\MeliPayamakService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class OtpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private string $mobile = '09123456789';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.melipayamak.enabled' => true,
            'services.melipayamak.username' => 'test-user',
            'services.melipayamak.api_key' => 'test-key',
            'services.melipayamak.pattern_code' => '156526',
            'services.melipayamak.pattern_approved' => false,
            'services.melipayamak.pattern_variable_count' => 2,
            'services.melipayamak.pattern_second_value' => 'ورود امن',
            'services.melipayamak.test_mode' => true,
            'services.melipayamak.otp_length' => 5,
            'services.melipayamak.otp_expire' => 120,
            'services.melipayamak.otp_max_attempts' => 5,
            'services.melipayamak.otp_resend_wait' => 60,
            'services.melipayamak.otp_daily_limit' => 20,
            'services.melipayamak.otp_ip_daily_limit' => 40,
            'app.debug' => true,
        ]);

        RateLimiter::clear('otp-cooldown:'.$this->mobile.':login');
        RateLimiter::clear('otp-daily:'.$this->mobile);
        RateLimiter::clear('otp-ip:127.0.0.1');
    }

    public function test_otp_generation_is_five_digits(): void
    {
        $otp = app(OtpService::class)->generateSecureOtp(5);

        $this->assertMatchesRegularExpression('/^\d{5}$/', $otp);
    }

    public function test_send_stores_hashed_otp_not_plaintext(): void
    {
        $result = app(OtpService::class)->send($this->mobile, 'login');

        $row = OtpCode::query()->latest('id')->first();

        $this->assertNotNull($row);
        $this->assertNotSame($result['debug_code'], $row->code_hash);
        $this->assertTrue(Hash::check($result['debug_code'], $row->code_hash));
        $this->assertTrue($result['simulated']);
    }

    public function test_correct_otp_verifies_and_cannot_be_reused(): void
    {
        $service = app(OtpService::class);
        $sent = $service->send($this->mobile, 'login');

        $this->assertTrue($service->verify($this->mobile, $sent['debug_code'], 'login'));

        $this->expectException(ValidationException::class);
        $service->verify($this->mobile, $sent['debug_code'], 'login');
    }

    public function test_wrong_otp_increments_attempts(): void
    {
        $service = app(OtpService::class);
        $service->send($this->mobile, 'login');

        try {
            $service->verify($this->mobile, '00000', 'login');
            $this->fail('Expected ValidationException');
        } catch (ValidationException) {
            // expected
        }

        $this->assertSame(1, OtpCode::query()->latest('id')->first()->attempts);
    }

    public function test_expired_otp_is_rejected(): void
    {
        $service = app(OtpService::class);
        $sent = $service->send($this->mobile, 'login');

        OtpCode::query()->latest('id')->first()->forceFill([
            'expires_at' => now()->subSecond(),
        ])->save();

        $this->expectException(ValidationException::class);
        $service->verify($this->mobile, $sent['debug_code'], 'login');
    }

    public function test_resend_rate_limit(): void
    {
        $service = app(OtpService::class);
        $service->send($this->mobile, 'login');

        $this->expectException(ValidationException::class);
        $service->send($this->mobile, 'login');
    }

    public function test_unapproved_pattern_blocks_when_test_mode_disabled(): void
    {
        config([
            'services.melipayamak.pattern_approved' => false,
            'services.melipayamak.test_mode' => false,
        ]);

        $this->expectException(ValidationException::class);
        app(OtpService::class)->send($this->mobile, 'login');
    }

    public function test_approval_guard_never_dispatches_real_provider_request_in_test_mode(): void
    {
        config([
            'services.melipayamak.pattern_approved' => false,
            'services.melipayamak.test_mode' => true,
        ]);

        $service = Mockery::mock(MeliPayamakService::class)->makePartial();
        $service->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('dispatchPattern')->never();

        $result = $service->sendOtpPattern($this->mobile, '12345');

        $this->assertTrue($result->success);
        $this->assertTrue($result->simulated);
        $this->assertNull($result->providerResponse);
    }

    public function test_approval_guard_throws_and_never_dispatches_when_test_mode_off(): void
    {
        config([
            'services.melipayamak.pattern_approved' => false,
            'services.melipayamak.test_mode' => false,
        ]);

        $service = Mockery::mock(MeliPayamakService::class)->makePartial();
        $service->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('dispatchPattern')->never();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('الگوی پیامک هنوز تأیید نشده است');

        $service->sendOtpPattern($this->mobile, '12345');
    }

    public function test_provider_error_codes_are_not_treated_as_success(): void
    {
        $service = app(MeliPayamakService::class);

        $this->assertFalse($service->isSuccessfulProviderResponse(0));
        $this->assertFalse($service->isSuccessfulProviderResponse(-1));
        $this->assertFalse($service->isSuccessfulProviderResponse('-5'));
        $this->assertFalse($service->isSuccessfulProviderResponse('error'));
        $this->assertFalse($service->isSuccessfulProviderResponse(''));
        $this->assertTrue($service->isSuccessfulProviderResponse(1234567890));
        $this->assertTrue($service->isSuccessfulProviderResponse('987654321'));
    }

    public function test_api_key_config_and_token_order_contract(): void
    {
        config([
            'services.melipayamak.username' => 'panel-user',
            'services.melipayamak.api_key' => 'secret-api-key',
            'services.melipayamak.pattern_variable_count' => 2,
            'services.melipayamak.pattern_second_value' => 'ورود امن',
            'services.melipayamak.pattern_code' => '156526',
            'services.melipayamak.driver' => 'soap',
        ]);

        $service = app(MeliPayamakService::class);

        // Contract used by dispatchPattern():
        // MelipayamakApi($username, $apiKey) then sms('soap')->sendByBaseNumber([$otp, $second], $mobile, $bodyId)
        $this->assertSame('panel-user', (string) config('services.melipayamak.username'));
        $this->assertSame('secret-api-key', (string) config('services.melipayamak.api_key'));
        $this->assertSame('soap', (string) config('services.melipayamak.driver'));
        $this->assertSame('156526', $service->bodyId());
        $this->assertSame(['54321', 'ورود امن'], $service->buildPatternTokens('54321'));
    }

    public function test_provider_failure_surfaces_persian_error(): void
    {
        config(['services.melipayamak.pattern_approved' => true, 'services.melipayamak.test_mode' => false]);

        $mock = Mockery::mock(MeliPayamakService::class);
        $mock->shouldReceive('sendOtpPattern')->once()->andReturn(
            new MeliPayamakSendResult(false, 'provider failed', '0')
        );
        $this->app->instance(MeliPayamakService::class, $mock);

        $this->expectException(ValidationException::class);
        app(OtpService::class)->send($this->mobile, 'login');
    }

    public function test_provider_success_path(): void
    {
        config(['services.melipayamak.pattern_approved' => true, 'services.melipayamak.test_mode' => false]);

        $mock = Mockery::mock(MeliPayamakService::class);
        $mock->shouldReceive('sendOtpPattern')->once()->andReturn(
            new MeliPayamakSendResult(true, 'ok', '1234567890', false, '1234567890')
        );
        $this->app->instance(MeliPayamakService::class, $mock);

        $result = app(OtpService::class)->send($this->mobile, 'login');

        $this->assertFalse($result['simulated']);
        $this->assertNull($result['debug_code']);
        $this->assertDatabaseCount('otp_codes', 1);
    }

    public function test_pattern_token_count_matches_config(): void
    {
        $service = app(MeliPayamakService::class);

        config(['services.melipayamak.pattern_variable_count' => 2]);
        $tokens = $service->buildPatternTokens('12345');
        $this->assertSame(['12345', 'ورود امن'], $tokens);

        config(['services.melipayamak.pattern_variable_count' => 1]);
        $this->assertSame(['12345'], $service->buildPatternTokens('12345'));
    }

    public function test_mobile_normalization(): void
    {
        $service = app(OtpService::class);

        $this->assertSame('09123456789', $service->normalizeMobile('9123456789'));
        $this->assertSame('09123456789', $service->normalizeMobile('+989123456789'));
        $this->assertSame('09123456789', $service->normalizeMobile('0912 345 6789'));
    }
}
