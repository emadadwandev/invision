<?php

namespace Tests\Feature;

use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresentationApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_list_report_templates(): void
    {
        ReportTemplate::create([
            'name' => 'Test Template',
            'type' => 'overview',
            'config' => ['entity' => 'sales_orders'],
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/report-templates');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Test Template']);
    }

    public function test_create_report_template(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/report-templates', [
            'name' => 'Monthly Sales',
            'type' => 'sales',
            'config' => ['entity' => 'sales_orders', 'filters' => ['status' => 'delivered']],
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Monthly Sales', 'type' => 'sales']);

        $this->assertDatabaseHas('report_templates', ['name' => 'Monthly Sales']);
    }

    public function test_create_report_template_validation(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/report-templates', [
            'name' => '',
            'type' => 'invalid_type',
        ]);

        $response->assertStatus(422);
    }

    public function test_update_report_template(): void
    {
        $template = ReportTemplate::create([
            'name' => 'Old Name',
            'type' => 'custom',
            'config' => ['entity' => 'stores'],
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/report-templates/{$template->id}", [
            'name' => 'New Name',
            'is_favorite' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $template->refresh();
        $this->assertTrue($template->is_favorite);
    }

    public function test_delete_report_template(): void
    {
        $template = ReportTemplate::create([
            'name' => 'To Delete',
            'type' => 'custom',
            'config' => ['entity' => 'stores'],
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/report-templates/{$template->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('report_templates', ['id' => $template->id]);
    }

    public function test_presentation_endpoints_require_auth(): void
    {
        $this->getJson('/api/v1/report-templates')->assertStatus(401);
        $this->postJson('/api/v1/report-templates')->assertStatus(401);
        $this->getJson('/api/v1/presentations/templates')->assertStatus(401);
        $this->postJson('/api/v1/presentations/generate')->assertStatus(401);
        $this->getJson('/api/v1/saved-exports')->assertStatus(401);
    }
}
