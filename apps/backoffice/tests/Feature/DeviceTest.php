<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Device;
use App\Models\Outlet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DeviceTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_cannot_be_moved_directly_to_another_outlet(): void
    {
        $business = Business::factory()->create();
        $source = $this->createOutlet($business, 'KU', 'PC-KU');
        $target = $this->createOutlet($business, 'KT', 'PC-KT');
        $device = Device::query()->create([
            'business_id' => $business->id,
            'outlet_id' => $source->id,
            'code' => 'POS-01',
            'name' => 'Tablet Kasir',
            'platform' => 'android',
            'installation_id' => (string) Str::uuid(),
            'status' => 'active',
        ]);

        try {
            $device->update(['outlet_id' => $target->id]);
            $this->fail('Perangkat dapat dipindahkan tanpa registrasi ulang.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Cabut perangkat lalu registrasikan ulang untuk mengganti outlet.',
                $exception->errors()['outlet_id'][0],
            );
        }

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'outlet_id' => $source->id,
        ]);
    }

    private function createOutlet(
        Business $business,
        string $code,
        string $profitCenter,
    ): Outlet {
        return Outlet::query()->create([
            'business_id' => $business->id,
            'code' => $code,
            'name' => "Kedai {$code}",
            'profit_center_code' => $profitCenter,
            'receipt_prefix' => $code,
            'status' => 'active',
        ]);
    }
}
