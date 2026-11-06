<?php

namespace Tests\Feature;

use App\Enums\PlatformContactMessageStatus;
use App\Models\PlatformContactMessage;
use App\Models\User;
use App\Notifications\NewPlatformContactMessageNotification;
use App\Notifications\PlatformContactReplyNotification;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PlatformContactMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminSeeder::class);
    }

    public function test_public_can_submit_contact_message(): void
    {
        Notification::fake();

        $this->post(route('public.contact.store'), [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'subject' => 'Listing question',
            'body' => 'How do I verify my dorm listing?',
        ])
            ->assertRedirect();

        $message = PlatformContactMessage::query()->first();
        $this->assertNotNull($message);
        $this->assertSame(PlatformContactMessageStatus::Pending, $message->status);
        $this->assertStringStartsWith('CNT-', $message->reference);

        Notification::assertSentTo(
            $this->superAdmin(),
            NewPlatformContactMessageNotification::class,
        );
    }

    public function test_super_admin_can_reply_to_contact_message(): void
    {
        Notification::fake();

        $message = PlatformContactMessage::query()->create([
            'reference' => 'CNT-TEST1234',
            'name' => 'Maria',
            'email' => 'maria@example.com',
            'subject' => 'Help',
            'body' => 'Hello',
            'status' => PlatformContactMessageStatus::Pending,
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route('platform.contact-messages.reply', $message), [
                'body' => 'Thanks for reaching out — here is how verification works.',
            ])
            ->assertRedirect();

        $message->refresh();
        $this->assertSame(PlatformContactMessageStatus::Replied, $message->status);
        $this->assertCount(1, $message->replies);

        Notification::assertSentOnDemand(PlatformContactReplyNotification::class);
    }

    public function test_public_can_view_thread_with_reference_and_email(): void
    {
        $message = PlatformContactMessage::query()->create([
            'reference' => 'CNT-PUBLIC01',
            'name' => 'Juan',
            'email' => 'juan@example.com',
            'subject' => 'Test',
            'body' => 'Question',
            'status' => PlatformContactMessageStatus::Replied,
        ]);

        $this->get(route('public.contact.show', [
            'reference' => $message->reference,
            'email' => 'juan@example.com',
        ]))
            ->assertOk()
            ->assertSee('Question');
    }

    private function superAdmin(): User
    {
        return User::query()->where('account_type', 'super_admin')->firstOrFail();
    }
}
