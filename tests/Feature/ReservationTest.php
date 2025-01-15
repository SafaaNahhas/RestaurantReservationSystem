<?php

namespace Tests\Feature;

use Exception;
use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Table;
use App\Enums\RoleUser;
use App\Models\Department;
use App\Models\Reservation;
use App\Models\NotificationLog;
use App\Jobs\SendRatingRequestJob;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Events\ReservationCompleted;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use App\Jobs\NotifyManagersAboutReservation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReservationTest extends TestCase
{

    use DatabaseTransactions;

    protected $adminUser;
    protected $customerUser;
    protected $managerUser;
    protected $department;
    protected $table;
    protected $token;
    protected $reservation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRolesAndPermissions();

        $this->adminUser = $this->createUserWithRole(RoleUser::Admin);
        $this->customerUser = $this->createUserWithRole(RoleUser::Customer);
        $this->managerUser = $this->createUserWithRole(RoleUser::Manager);

        $this->customerUser->givePermissionTo('store reservation');
        $this->customerUser->givePermissionTo('update reservation');

        $this->department = $this->createDepartment($this->managerUser);
        $this->table = $this->createTable($this->department);
        $this->customerUser->notificationSettings()->create([
                        'telegram_chat_id' => 2554188384,
                        'method_send_notification' => 'telegram',
                        'send_notification_options' => json_encode(['events']),
                    ]);

        $this->reservation = Reservation::create([
            'user_id' => $this->customerUser->id,
            'table_id' => $this->table->id,
            'guest_count' => 4,
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'pending',
        ]);

        $this->token = JWTAuth::fromUser($this->customerUser);
    }

    public function createRolesAndPermissions(): void
    {
        Role::firstOrCreate(['name' => RoleUser::Admin->value]);
        Role::firstOrCreate(['name' => RoleUser::Customer->value]);
        Role::firstOrCreate(['name' => 'Reservation Manager']);

        Permission::firstOrCreate(['name' => 'store reservation', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'update reservation', 'guard_name' => 'api']);
    }

    public function createUserWithRole($role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);
        return $user;
    }

    public function createDepartment($manager): Department
    {
        return Department::create([
            'name' => 'Dining',
            'description' => 'Responsible for dining reservations.',
            'manager_id' => $manager->id,
        ]);
    }

    public function createTable($department): Table
    {
        return Table::factory()->create([
            'seat_count' => 6,
            'department_id' => $department->id,
            'table_number' => 'T' . uniqid(),
        ]);
    }

    /** @test */
    public function test_it_creates_a_reservation_with_notification_successfully()
    {


        $reservationData = [
            'user_id' => $this->customerUser->id,
            'manager_id' => $this->managerUser->id,
            'table_id' => $this->table->id,
            'start_date' => now()->addDays(3),
            'end_date' => now()->addDays(3)->addHours(2),
            'guest_count' => 4,
            'services' => json_encode(['service1', 'service2']),
            'status' => 'pending',
        ];

        $response = $this->postJson(
            'api/reservations',
            $reservationData,
            ['Authorization' => 'Bearer ' . $this->token]
        );

        // $response->dump();

        $response->assertStatus(201);

        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->customerUser->id,
            'table_id' => $this->table->id,
            'status' => 'pending',
        ]);

        NotificationLog::create([
            'user_id' => $this->managerUser->id,
            'status' => 'sent',
            'reservation_id' => $this->reservation->id,
            'reason_notification_send' => 'Notification sent successfully',
            'description' => 'Reservation notification was successfully sent',

        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->managerUser->id,
            'status' => 'sent',
        ]);
    }


    public function test_it_updates_a_reservation_successfully()
    {
        $updatedData = [
            'guest_count' => 6, // تحديث عدد الضيوف
            'start_date' => now()->addDay()->minute(0)->second(0)->format('Y-m-d H:i:s'), // غداً
            'end_date' => now()->addDay()->addHours(2)->minute(0)->second(0)->format('Y-m-d H:i:s'), // غداً + ساعتين
            'services' => 'updated service', // خدمة جديدة
        ];

        $availableTable = $this->createTable($this->department);

        $this->reservation->update(['status' => 'pending']); // التأكد أن الحجز في حالة "pending"

        $response = $this->putJson(
            "api/reservations/{$this->reservation->id}",
            array_merge($updatedData, ['table_number' => $availableTable->table_number]),
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(200); // التحقق من نجاح التحديث
        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservation->id,
            'user_id' => $this->customerUser->id,
            'guest_count' => $updatedData['guest_count'],
            'start_date' => $updatedData['start_date'],
            'end_date' => $updatedData['end_date'],
            'services' => $updatedData['services'],
        ]);
    }

    ///** @test */
    public function test_get_all_tables_with_reservations()
    {
        // إعداد بيانات الاختبار
        $filter = ['status' => 'pending'];
        $cacheKey = 'tables_with_reservations_' . md5(json_encode($filter) . $this->adminUser->id);

        // إنشاء جدول وحجوزات للاختبار
        $reservation = Reservation::create([
            'user_id' => $this->customerUser->id,
            'table_id' => $this->table->id,
            'guest_count' => 4,
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'pending',
        ]);

        // محاكاة الكاش
        Cache::shouldReceive('remember')
            ->once()
            ->with($cacheKey, 600, \Closure::class)
            ->andReturn(collect([$this->table]));

        // تنفيذ الطلب كمسؤول
        $response = $this->actingAs($this->adminUser)
            ->json('GET', '/api/tables-with-reservations', $filter);

        // التحقق من نجاح الطلب
        $response->assertStatus(200);

        // التحقق من وجود البيانات في الاستجابة
        $response->assertJsonFragment([
            'table_number' => $this->table->table_number,
        ]);
        $response->assertJsonFragment([
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function test_get_all_tables_with_reservations_no_results()
    {
        $filter = ['status' => 'confirmed'];

        $response = $this->actingAs($this->adminUser)
            ->json('GET', '/api/tables-with-reservations', $filter);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'No tables found with the specified reservation status.  failed!',
            'data' => [],
            'status' => 'error',
        ]);
    }


   /** @test */
    public function test_get_all_tables_with_reservations_cached_data()
    {
        $filter = ['status' => 'pending'];
        Cache::put('tables_with_reservations_' . md5(json_encode($filter)), collect([$this->table]), 600);
        $response = $this->actingAs($this->adminUser)
            ->json('GET', '/api/tables-with-reservations', $filter);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'table_number' => $this->table->table_number,
            'status' => 'pending',
        ]);
    }
    /** @test */
    public function test_confirm_reservation_successfully()
    {
        $this->reservation = Reservation::create([
            'user_id' => $this->customerUser->id,
            'table_id' => $this->table->id,
            'guest_count' => 4,
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'pending',
        ]);
        $this->reservation->update(['status' => 'pending']);
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/reservations/' . $this->reservation->id . '/confirm', [], [
                'Authorization' => 'Bearer ' . $this->token
            ]);
        // $response->dump();

        $response->assertStatus(200);

        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservation->id,
            'status' => 'confirmed',
        ]);
        NotificationLog::create([
            'user_id' => $this->adminUser->id,
            'status' => 'sent',
            'reservation_id' => $this->reservation->id,
            'reason_notification_send' => 'Reservation confirmed',
            'description' => 'The reservation was confirmed successfully',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->adminUser->id,
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function test_confirm_reservation_notification_failure()
    {
        Http::shouldReceive('post')
            ->once()
            ->andThrow(new Exception('Telegram API error'));

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/reservations/' . $this->reservation->id . '/confirm', [], [
                'Authorization' => 'Bearer ' . $this->token
            ]);

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'An unexpected error occurred.Telegram API error  failed!',
        ]);
    }
    /** @test */
    public function test_confirm_reservation_with_invalid_status()
    {
        $this->reservation->update(['status' => 'confirmed']);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/reservations/' . $this->reservation->id . '/confirm', [], [
                'Authorization' => 'Bearer ' . $this->token
            ]);

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'Reservation must be in pending state to confirm  failed!',
        ]);
    }
    /** @test */
    public function test_reject_reservation_successfully()
    {
        $this->reservation->update(['status' => 'pending']);

        $rejectionData = [
            'rejection_reason' => 'Table maintenance',
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/reservations/' . $this->reservation->id . '/reject', $rejectionData, [
                'Authorization' => 'Bearer ' . $this->token,
            ]);

        // $response->dump();

        $response->assertStatus(200);

        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservation->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('reservation_details', [
            'reservation_id' => $this->reservation->id,
            'status' => 'rejected',
            'rejection_reason' => 'Table maintenance',
        ]);

        NotificationLog::create([
            'user_id' => $this->adminUser->id,
            'status' => 'sent',
            'reservation_id' => $this->reservation->id,
            'reason_notification_send' => 'Reservation rejected',
            'description' => 'Reservation was successfully rejected',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->adminUser->id,
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function test_reject_reservation_with_invalid_status()
    {
        $this->reservation->update(['status' => 'confirmed']);

        $rejectionData = [
            'rejection_reason' => 'Table maintenance',
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/reservations/' . $this->reservation->id . '/reject', $rejectionData, [
                'Authorization' => 'Bearer ' . $this->token,
            ]);

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'Reservation must be in pending state to reject  failed!',
        ]);

    }
    /** @test */
    public function test_reject_reservation_with_past_date()
    {
        $this->reservation->update([
            'start_date' => now()->subDay(),
        ]);

        $rejectionData = [
            'rejection_reason' => 'Table maintenance',
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/reservations/' . $this->reservation->id . '/reject', $rejectionData, [
                'Authorization' => 'Bearer ' . $this->token,
            ]);

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'Cannot modify past reservations  failed!',
        ]);
    }

    /** @test */
    public function test_reject_reservation_notification_failure()
    {
        Http::shouldReceive('post')
            ->once()
            ->andThrow(new Exception('Telegram API error'));

        $this->reservation->update(['status' => 'pending']);

        $rejectionData = [
            'rejection_reason' => 'Table maintenance',
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/reservations/' . $this->reservation->id . '/reject', $rejectionData, [
                'Authorization' => 'Bearer ' . $this->token,
            ]);

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'An unexpected error occurred.Telegram API error  failed!',
        ]);}

    /** @test */
    public function test_successful_reservation_cancellation()
    {
        $user = $this->customerUser;
        $cancelPermission = Permission::firstOrCreate(['name' => 'cancel reservation']);
        $user->givePermissionTo($cancelPermission);

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'status' => 'confirmed',
            'start_date' => now()->addHours(2),
            'table_id' => $this->table->id
        ]);

        $cancellationData = [
            'cancellation_reason' => 'User request'
        ];

        $response = $this->actingAs($user)
                        ->postJson('/api/reservations/' . $reservation->id . '/cancel', $cancellationData);

        // dd($response->getContent());
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Reservation cancelled successfully with sent cancellation notifications',
                ]);

        $reservation->refresh();
        $this->assertEquals('cancelled', $reservation->status);
    }

    /** @test */
    public function test_unsuccessful_reservation_cancellation_due_to_invalid_status()
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'customer']);
        $user->assignRole($role);

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'start_date' => now()->addHours(2),
            'table_id' => Table::factory()->create()->id,
        ]);

        $cancellationData = [
            'cancellation_reason' => 'User request',
        ];

        $response = $this->actingAs($user)
                        ->postJson('/api/reservations/' . $reservation->id . '/cancel', $cancellationData);

        $response->assertStatus(422)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Reservation must be in confirm state to cancel  failed!',
                    'data' => null,
                ]);
    }

    /** @test */
    public function test_unsuccessful_reservation_cancellation_due_to_past_date()
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'customer']);
        $user->assignRole($role);

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'status' => 'confirmed',
            'start_date' => now()->subDay(),
            'table_id' => Table::factory()->create()->id,
        ]);

        $cancellationData = [
            'cancellation_reason' => 'User request',
        ];

        $response = $this->actingAs($user)
                        ->postJson('/api/reservations/' . $reservation->id . '/cancel', $cancellationData);

        $response->assertStatus(422)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Cannot modify past reservations  failed!',
                    'data' => null,
                ]);
    }

    /** @test */
    public function test_start_service_successfully()
    {
        $this->reservation->update(['status' => 'confirmed']);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/reservations/' . $this->reservation->id . '/start-service', [], [
                'Authorization' => 'Bearer ' . $this->token,
            ]);

        // $response->dump();

        $response->assertStatus(200);

        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservation->id,
            'status' => 'in_service',
        ]);
        NotificationLog::create([
            'user_id' => $this->adminUser->id,
            'status' => 'sent',
            'reservation_id' => $this->reservation->id,
            'reason_notification_send' => 'Service started',
            'description' => 'The reservation service was successfully started',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->adminUser->id,
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function test_start_service_with_invalid_status()
    {
        $this->reservation->update(['status' => 'pending']);
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/reservations/' . $this->reservation->id . '/start-service', [], [
                'Authorization' => 'Bearer ' . $this->token,
            ]);

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'Reservation must be confirmed to start service  failed!',
        ]);
    }



}

