<?php

namespace Tests\Feature;

class ApiResourceRoleGatingTest extends GracimorTestCase
{
    // ── Loan resource ─────────────────────────────────────────────────────────

    /** @test */
    public function loan_resource_includes_approval_details_for_manager(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID,
            $this->asManager()
        );

        $data = $response->json('data');
        $this->assertArrayHasKey('approved_by', $data);
        $this->assertArrayHasKey('approved_at', $data);
        $this->assertArrayHasKey('disbursed_by', $data);
    }

    /** @test */
    public function loan_resource_excludes_approval_details_for_officer(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID,
            $this->asOfficer()
        );

        $data = $response->json('data');
        $this->assertArrayNotHasKey('approved_by', $data);
        $this->assertArrayNotHasKey('approved_at', $data);
    }

    /** @test */
    public function loan_resource_includes_ltv_for_manager_and_above(): void
    {
        $managerResponse = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID,
            $this->asManager()
        );
        $this->assertArrayHasKey('ltv_at_origination', $managerResponse->json('data'));

        $officerResponse = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID,
            $this->asOfficer()
        );
        $this->assertArrayNotHasKey('ltv_at_origination', $officerResponse->json('data'));
    }

    /** @test */
    public function loan_resource_includes_rejection_reason_for_manager(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_REJECTED_ID,
            $this->asManager()
        );

        $this->assertArrayHasKey('rejection_reason', $response->json('data'));
        $this->assertNotNull($response->json('data.rejection_reason'));
    }

    /** @test */
    public function loan_resource_omits_rejection_reason_for_officer(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_REJECTED_ID,
            $this->asOfficer()
        );

        $this->assertArrayNotHasKey('rejection_reason', $response->json('data'));
    }

    // ── Borrower resource ─────────────────────────────────────────────────────

    /** @test */
    public function borrower_resource_includes_monthly_income_for_officer_and_above(): void
    {
        foreach ([$this->asOfficer(), $this->asManager(), $this->asCeo()] as $headers) {
            $response = $this->getJson(
                '/api/borrowers/' . TestingSeeder::BORROWER_VERIFIED_ID,
                $headers
            );
            $this->assertArrayHasKey('monthly_income', $response->json('data'));
        }
    }

    /** @test */
    public function borrower_resource_excludes_monthly_income_for_accountant(): void
    {
        $response = $this->getJson(
            '/api/borrowers/' . TestingSeeder::BORROWER_VERIFIED_ID,
            $this->asAccountant()
        );
        $this->assertArrayNotHasKey('monthly_income', $response->json('data'));
    }

    /** @test */
    public function borrower_resource_includes_kyc_verified_at_for_manager(): void
    {
        $response = $this->getJson(
            '/api/borrowers/' . TestingSeeder::BORROWER_VERIFIED_ID,
            $this->asManager()
        );
        $this->assertArrayHasKey('kyc_verified_at', $response->json('data'));
    }

    // ── Penalty resource ──────────────────────────────────────────────────────

    /** @test */
    public function penalty_resource_includes_waiver_fields_for_manager(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_OVERDUE_ID . '/penalties',
            $this->asManager()
        );

        $first = $response->json('data.0');
        $this->assertArrayHasKey('waiver_reason', $first);
        $this->assertArrayHasKey('waived_by', $first);
        $this->assertArrayHasKey('waived_at', $first);
    }

    /** @test */
    public function penalty_resource_omits_waiver_fields_for_officer(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_OVERDUE_ID . '/penalties',
            $this->asOfficer()
        );

        $first = $response->json('data.0');
        $this->assertArrayNotHasKey('waiver_reason', $first);
        $this->assertArrayNotHasKey('waived_by', $first);
    }

    // ── Payment resource ──────────────────────────────────────────────────────

    /** @test */
    public function payment_resource_includes_reversal_details_for_manager(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/payments',
            $this->asManager()
        );

        $first = $response->json('data.0');
        $this->assertArrayHasKey('reversal_reason', $first);
        $this->assertArrayHasKey('reversed_at', $first);
    }

    /** @test */
    public function payment_resource_omits_reversal_details_for_officer(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/payments',
            $this->asOfficer()
        );

        $first = $response->json('data.0');
        $this->assertArrayNotHasKey('reversal_reason', $first);
        $this->assertArrayNotHasKey('reversed_at', $first);
    }

    // ── HATEOAS links ─────────────────────────────────────────────────────────

    /** @test */
    public function loan_resource_includes_approve_link_for_manager_on_pending_loan(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_PENDING_ID,
            $this->asManager()
        );

        $links = $response->json('data.links');
        $actions = collect($links)->pluck('rel')->all();
        $this->assertContains('approve', $actions);
        $this->assertContains('reject', $actions);
    }

    /** @test */
    public function loan_resource_excludes_approve_link_for_officer(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_PENDING_ID,
            $this->asOfficer()
        );

        $links   = $response->json('data.links') ?? [];
        $actions = collect($links)->pluck('rel')->all();
        $this->assertNotContains('approve', $actions);
    }

    /** @test */
    public function active_loan_resource_includes_record_payment_link(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID,
            $this->asOfficer()
        );

        $links   = $response->json('data.links') ?? [];
        $actions = collect($links)->pluck('rel')->all();
        $this->assertContains('record_payment', $actions);
    }

    /** @test */
    public function closed_loan_resource_excludes_record_payment_link(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_CLOSED_ID,
            $this->asOfficer()
        );

        $links   = $response->json('data.links') ?? [];
        $actions = collect($links)->pluck('rel')->all();
        $this->assertNotContains('record_payment', $actions);
    }

    // ── User resource ─────────────────────────────────────────────────────────

    /** @test */
    public function superadmin_can_list_all_users(): void
    {
        $response = $this->getJson('/api/users', $this->asSuperadmin());
        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'role', 'email', 'is_active']]]);
    }

    /** @test */
    public function officer_cannot_list_users(): void
    {
        $this->getJson('/api/users', $this->asOfficer())
            ->assertForbidden();
    }

    /** @test */
    public function user_resource_omits_password_for_all_roles(): void
    {
        foreach ([
            $this->asSuperadmin(), $this->asCeo(),
            $this->asManager(), $this->asOfficer(),
        ] as $headers) {
            $response = $this->getJson('/api/auth/me', $headers);
            $this->assertArrayNotHasKey('password', $response->json('data'));
        }
    }

    // ── Status badge ──────────────────────────────────────────────────────────

    /** @test */
    public function loan_resource_includes_status_badge_meta(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_OVERDUE_ID,
            $this->asOfficer()
        );

        $this->assertArrayHasKey('status_badge', $response->json('data'));
        $badge = $response->json('data.status_badge');
        $this->assertArrayHasKey('label', $badge);
        $this->assertArrayHasKey('colour', $badge);
        $this->assertEquals('overdue', $badge['label']);
        $this->assertEquals('red', $badge['colour']);
    }
}
