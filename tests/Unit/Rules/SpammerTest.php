<?php

namespace Tests\Unit\Rules;

use App\Rules\Spammer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SpammerTest extends TestCase
{
    public function test_spammer_rule_passes_for_valid_email(): void
    {
        Http::fake([
            'https://grouphome.guide/api/spam' => Http::response(['spam1@example.com', 'spam2@example.com']),
        ]);

        config(['spam' => ['localspam@example.com']]);

        $rule = new Spammer;
        $passed = true;

        $rule->validate('email', 'valid@example.com', function () use (&$passed) {
            $passed = false;
        });

        $this->assertTrue($passed);
    }

    public function test_spammer_rule_fails_for_spam_email_from_api(): void
    {
        Http::fake([
            'https://grouphome.guide/api/spam' => Http::response(['spam@example.com', 'spam2@example.com']),
        ]);

        config(['spam' => []]);

        $rule = new Spammer;
        $passed = true;

        $rule->validate('email', 'spam@example.com', function () use (&$passed) {
            $passed = false;
        });

        $this->assertFalse($passed);
    }

    public function test_spammer_rule_fails_for_spam_email_from_config(): void
    {
        Http::fake([
            'https://grouphome.guide/api/spam' => Http::response([]),
        ]);

        config(['spam' => ['configspam@example.com', 'anotherspam@example.com']]);

        $rule = new Spammer;
        $passed = true;

        $rule->validate('email', 'configspam@example.com', function () use (&$passed) {
            $passed = false;
        });

        $this->assertFalse($passed);
    }

    public function test_spammer_rule_handles_wildcard_patterns(): void
    {
        Http::fake([
            'https://grouphome.guide/api/spam' => Http::response(['*@spam-domain.com']),
        ]);

        config(['spam' => ['*@bad-domain.net']]);

        $rule = new Spammer;

        // Test API wildcard
        $passed = true;
        $rule->validate('email', 'anything@spam-domain.com', function () use (&$passed) {
            $passed = false;
        });
        $this->assertFalse($passed);

        // Test config wildcard
        $passed = true;
        $rule->validate('email', 'user@bad-domain.net', function () use (&$passed) {
            $passed = false;
        });
        $this->assertFalse($passed);
    }

    public function test_spammer_rule_handles_api_failure_gracefully(): void
    {
        Http::fake([
            'https://grouphome.guide/api/spam' => Http::response(null, 500),
        ]);

        config(['spam' => ['configspam@example.com']]);

        $rule = new Spammer;

        // Should still work with config spam list even if API fails
        $passed = true;
        $rule->validate('email', 'configspam@example.com', function () use (&$passed) {
            $passed = false;
        });
        $this->assertFalse($passed);

        // Should pass for valid email even if API fails
        $passed = true;
        $rule->validate('email', 'valid@example.com', function () use (&$passed) {
            $passed = false;
        });
        $this->assertTrue($passed);
    }

    public function test_spammer_rule_merges_and_deduplicates_spam_lists(): void
    {
        Http::fake([
            'https://grouphome.guide/api/spam' => Http::response(['spam@example.com', 'duplicate@example.com']),
        ]);

        config(['spam' => ['duplicate@example.com', 'configspam@example.com']]);

        $rule = new Spammer;

        // Both should fail (testing that merge works)
        $passed = true;
        $rule->validate('email', 'spam@example.com', function () use (&$passed) {
            $passed = false;
        });
        $this->assertFalse($passed);

        $passed = true;
        $rule->validate('email', 'configspam@example.com', function () use (&$passed) {
            $passed = false;
        });
        $this->assertFalse($passed);

        // Duplicate should also fail (testing that unique works)
        $passed = true;
        $rule->validate('email', 'duplicate@example.com', function () use (&$passed) {
            $passed = false;
        });
        $this->assertFalse($passed);
    }
}
