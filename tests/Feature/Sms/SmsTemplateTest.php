<?php

namespace Tests\Feature\Sms;

use Database\Seeders\TestingSeeder;

class SmsTemplateTest extends GracimorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SmsTemplateSeeder::class); // seed SMS templates on top of TestingSeeder
    }

    // ── List ──────────────────────────────────────────────────────────────────

    /** @test */
    public function manager_can_list_sms_templates(): void
    {
        $response = $this->getJson('/api/sms-templates', $this->asManager());

        $response->assertOk()
            ->assertJsonStructure([
                'templates' => [['id', 'trigger_key', 'name', 'category', 'is_active']],
                'categories',
            ]);

        $this->assertGreaterThanOrEqual(12, count($response->json('templates')));
    }

    /** @test */
    public function officer_cannot_list_sms_templates(): void
    {
        $this->getJson('/api/sms-templates', $this->asOfficer())
            ->assertForbidden();
    }

    /** @test */
    public function all_seeded_trigger_keys_appear_in_list(): void
    {
        $response   = $this->getJson('/api/sms-templates', $this->asManager());
        $triggerKeys = collect($response->json('templates'))->pluck('trigger_key')->all();

        $expected = [
            'payment_confirmation', 'loan_approved', 'loan_disbursed', 'loan_closed',
            'pre_due_7_days', 'pre_due_3_days', 'pre_due_1_day', 'due_today',
            'overdue_1_day', 'overdue_7_days', 'overdue_14_days', 'overdue_30_days',
        ];

        foreach ($expected as $key) {
            $this->assertContains($key, $triggerKeys, "Missing trigger key: {$key}");
        }
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    /** @test */
    public function manager_can_view_a_template_with_full_detail(): void
    {
        $template = SmsTemplate::where('trigger_key', 'payment_confirmation')->first();

        $response = $this->getJson("/api/sms-templates/{$template->id}", $this->asManager());

        $response->assertOk()
            ->assertJsonPath('trigger_key', 'payment_confirmation')
            ->assertJsonStructure([
                'id', 'trigger_key', 'name', 'body', 'category',
                'is_active', 'char_count', 'sms_pages',
                'variables', 'preview', 'max_length',
            ]);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    /** @test */
    public function manager_can_update_template_body(): void
    {
        $template = SmsTemplate::where('trigger_key', 'payment_confirmation')->first();

        $newBody = '{company_name}: Hi {first_name}, K{amount_paid} received for {loan_number}. Ref: {receipt}.';

        $response = $this->putJson(
            "/api/sms-templates/{$template->id}",
            ['body' => $newBody],
            $this->asManager()
        );

        $response->assertOk()
            ->assertJsonPath('template.body', $newBody);

        $this->assertDatabaseHas('sms_templates', [
            'id'   => $template->id,
            'body' => $newBody,
        ]);
    }

    /** @test */
    public function template_update_records_last_edited_by(): void
    {
        $template = SmsTemplate::where('trigger_key', 'payment_confirmation')->first();

        $this->putJson(
            "/api/sms-templates/{$template->id}",
            ['body' => '{company_name}: Updated. {first_name} {loan_number}.'],
            $this->asManager()
        )->assertOk();

        $this->assertDatabaseHas('sms_templates', [
            'id'             => $template->id,
            'last_edited_by' => TestingSeeder::MANAGER_ID,
        ]);
    }

    /** @test */
    public function template_update_flushes_cache(): void
    {
        $template = SmsTemplate::where('trigger_key', 'payment_confirmation')->first();

        // Seed a stale cache entry
        Cache::put("sms_template:payment_confirmation", $template, 3600);
        $this->assertTrue(Cache::has("sms_template:payment_confirmation"));

        $this->putJson(
            "/api/sms-templates/{$template->id}",
            ['name' => 'Updated Name'],
            $this->asManager()
        )->assertOk();

        // Cache entry for this template should be gone
        $this->assertFalse(Cache::has("sms_template:payment_confirmation"));
    }

    /** @test */
    public function template_update_rejects_unknown_variables(): void
    {
        $template = SmsTemplate::where('trigger_key', 'payment_confirmation')->first();

        $this->putJson(
            "/api/sms-templates/{$template->id}",
            ['body' => 'Hi {first_name}, your {magic_field} is ready.'],
            $this->asManager()
        )->assertUnprocessable()
         ->assertJsonFragment(['unknown_variables' => ['magic_field']]);
    }

    /** @test */
    public function template_update_validates_minimum_body_length(): void
    {
        $template = SmsTemplate::where('trigger_key', 'payment_confirmation')->first();

        $this->putJson(
            "/api/sms-templates/{$template->id}",
            ['body' => 'Hi'],
            $this->asManager()
        )->assertUnprocessable()
         ->assertJsonValidationErrors(['body']);
    }

    /** @test */
    public function officer_cannot_update_a_template(): void
    {
        $template = SmsTemplate::where('trigger_key', 'payment_confirmation')->first();

        $this->putJson(
            "/api/sms-templates/{$template->id}",
            ['body' => '{company_name}: {first_name} updated.'],
            $this->asOfficer()
        )->assertForbidden();
    }

    /** @test */
    public function manager_can_deactivate_a_template(): void
    {
        $template = SmsTemplate::where('trigger_key', 'pre_due_7_days')->first();

        $this->putJson(
            "/api/sms-templates/{$template->id}",
            ['is_active' => false],
            $this->asManager()
        )->assertOk()
         ->assertJsonPath('template.is_active', false);

        $this->assertDatabaseHas('sms_templates', [
            'id'        => $template->id,
            'is_active' => false,
        ]);
    }

    // ── Preview ───────────────────────────────────────────────────────────────

    /** @test */
    public function manager_can_preview_a_template_with_demo_values(): void
    {
        $template = SmsTemplate::where('trigger_key', 'payment_confirmation')->first();

        $response = $this->postJson(
            "/api/sms-templates/{$template->id}/preview",
            [],
            $this->asManager()
        );

        $response->assertOk()
            ->assertJsonStructure([
                'trigger_key', 'preview_body', 'char_count', 'sms_pages', 'variables',
            ]);

        // Preview body should not contain any unresolved {variables}
        $this->assertStringNotContainsString('{', $response->json('preview_body'));
    }

    /** @test */
    public function preview_char_count_matches_body_length(): void
    {
        $template = SmsTemplate::where('trigger_key', 'payment_confirmation')->first();

        $response = $this->postJson(
            "/api/sms-templates/{$template->id}/preview",
            [],
            $this->asManager()
        )->assertOk();

        $body      = $response->json('preview_body');
        $charCount = $response->json('char_count');

        $this->assertEquals(mb_strlen($body), $charCount);
    }

    // ── Auto char_count on save ───────────────────────────────────────────────

    /** @test */
    public function saving_a_template_auto_updates_char_count_and_pages(): void
    {
        $template = SmsTemplate::where('trigger_key', 'due_today')->first();
        $shortBody = '{company_name}: {first_name}, K{amount_due} due today. Pay now: {officer_phone}.';

        $this->putJson(
            "/api/sms-templates/{$template->id}",
            ['body' => $shortBody],
            $this->asManager()
        )->assertOk();

        $template->refresh();

        $this->assertGreaterThan(0, $template->char_count);
        $this->assertGreaterThanOrEqual(1, $template->sms_pages);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// ApiResourceRoleGatingTest
// File: tests/Feature/ApiResourceRoleGatingTest.php
//
// Verifies that JSON responses include or exclude fields based on the
// authenticated user's role, as implemented in the API Resource classes.
// ═══════════════════════════════════════════════════════════════════════════════

namespace Tests\Feature;
