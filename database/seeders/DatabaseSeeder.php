<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Models\UtilityReading;
use App\Models\UtilityType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo admin (landlord) account
        User::updateOrCreate(
            ['email' => 'admin@bms.local'],
            [
                'name' => 'Demo Landlord',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Utility types
        $utilities = [
            ['name' => 'Electricity', 'unit_label' => 'kWh', 'rate_per_unit' => '9.5000', 'flat_fee' => '0', 'is_metered' => true, 'active' => true],
            ['name' => 'Water',       'unit_label' => 'gal', 'rate_per_unit' => '0.3000', 'flat_fee' => '0', 'is_metered' => true, 'active' => true],
            ['name' => 'Gas',         'unit_label' => '',    'rate_per_unit' => '0',      'flat_fee' => '990', 'is_metered' => false, 'active' => true],
            ['name' => 'Service Charge', 'unit_label' => '', 'rate_per_unit' => '0',      'flat_fee' => '500', 'is_metered' => false, 'active' => true],
        ];
        foreach ($utilities as $u) {
            UtilityType::updateOrCreate(['name' => $u['name']], $u);
        }
        $elec = UtilityType::where('name', 'Electricity')->first();
        $water = UtilityType::where('name', 'Water')->first();
        $gas = UtilityType::where('name', 'Gas')->first();
        $service = UtilityType::where('name', 'Service Charge')->first();

        // Buildings
        $buildingsData = [
            ['name' => 'Dhanmondi Heights',  'address' => 'Road 7, Dhanmondi',   'city' => 'Dhaka',     'total_floors' => 6],
            ['name' => 'Gulshan Residency',  'address' => 'Road 41, Gulshan-2',  'city' => 'Dhaka',     'total_floors' => 8],
            ['name' => 'Chattogram Court',   'address' => 'CDA Avenue',          'city' => 'Chattogram','total_floors' => 4],
        ];
        $buildings = collect();
        foreach ($buildingsData as $b) {
            $buildings->push(Building::updateOrCreate(['name' => $b['name']], $b));
        }

        // Units per building
        $unitCount = 0;
        foreach ($buildings as $bIdx => $building) {
            $floorsToFill = min(4, $building->total_floors);
            for ($f = 1; $f <= $floorsToFill; $f++) {
                foreach (['A', 'B'] as $suffix) {
                    $rent = 15000 + ($bIdx * 5000) + ($f * 500) + ($suffix === 'B' ? 1000 : 0);
                    Unit::updateOrCreate(
                        ['building_id' => $building->id, 'unit_number' => "{$f}{$suffix}"],
                        [
                            'floor' => $f,
                            'bedrooms' => 2 + ($suffix === 'B' ? 1 : 0),
                            'bathrooms' => 2,
                            'size_sqft' => 900 + ($suffix === 'B' ? 300 : 0),
                            'base_rent' => $rent,
                            'status' => 'vacant',
                        ]
                    );
                    $unitCount++;
                }
            }
        }

        // Tenants
        $tenantNames = [
            ['Arif Hossain',   '+8801711111111', 'arif@example.com',   'Software Engineer'],
            ['Nusrat Jahan',   '+8801722222222', 'nusrat@example.com', 'Doctor'],
            ['Rafiq Ahmed',    '+8801733333333', 'rafiq@example.com',  'Banker'],
            ['Shamima Akter',  '+8801744444444', 'shamima@example.com','Teacher'],
            ['Tanvir Alam',    '+8801755555555', 'tanvir@example.com', 'Entrepreneur'],
            ['Farhana Rahman', '+8801766666666', 'farhana@example.com','Architect'],
            ['Kamal Uddin',    '+8801777777777', 'kamal@example.com',  'Civil Servant'],
            ['Sabina Yasmin',  '+8801788888888', 'sabina@example.com', 'Journalist'],
            ['Hasan Mahmud',   '+8801799999999', 'hasan@example.com',  'Consultant'],
            ['Rumana Islam',   '+8801700000001', 'rumana@example.com', 'Pharmacist'],
        ];
        $tenants = collect();
        foreach ($tenantNames as [$name, $phone, $email, $occupation]) {
            $tenants->push(Tenant::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name, 'phone' => $phone,
                    'occupation' => $occupation,
                    'national_id' => '19' . str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                ]
            ));
        }

        // Create leases — occupy ~70% of units
        $vacantUnits = Unit::where('status', 'vacant')->get();
        $leaseCount = min($tenants->count(), (int) ceil($vacantUnits->count() * 0.7));

        for ($i = 0; $i < $leaseCount; $i++) {
            $unit = $vacantUnits[$i];
            $tenant = $tenants[$i % $tenants->count()];

            $startMonthsAgo = random_int(3, 10);
            $lease = Lease::create([
                'tenant_id' => $tenant->id,
                'unit_id' => $unit->id,
                'start_date' => Carbon::now()->subMonths($startMonthsAgo)->startOfMonth(),
                'end_date' => null,
                'monthly_rent' => $unit->base_rent,
                'security_deposit' => $unit->base_rent * 2,
                'rent_due_day' => 5,
                'status' => 'active',
            ]);
            $unit->update(['status' => 'occupied']);

            // Generate invoices + readings for each month of the lease up to current
            $cursor = $lease->start_date->copy()->startOfMonth();
            $endCursor = Carbon::now()->startOfMonth();
            $prevElec = random_int(1200, 1800);
            $prevWater = random_int(5000, 8000);

            while ($cursor->lte($endCursor)) {
                // Electricity reading
                $elecConsumption = random_int(80, 220);
                $currElec = $prevElec + $elecConsumption;
                UtilityReading::updateOrCreate(
                    ['unit_id' => $unit->id, 'utility_type_id' => $elec->id, 'period_month' => $cursor->toDateString()],
                    [
                        'previous_reading' => $prevElec,
                        'current_reading' => $currElec,
                        'consumption' => $elecConsumption,
                        'amount' => (string) round($elecConsumption * $elec->rate_per_unit, 2),
                    ]
                );
                $prevElec = $currElec;

                // Water reading
                $waterConsumption = random_int(600, 1500);
                $currWater = $prevWater + $waterConsumption;
                UtilityReading::updateOrCreate(
                    ['unit_id' => $unit->id, 'utility_type_id' => $water->id, 'period_month' => $cursor->toDateString()],
                    [
                        'previous_reading' => $prevWater,
                        'current_reading' => $currWater,
                        'consumption' => $waterConsumption,
                        'amount' => (string) round($waterConsumption * $water->rate_per_unit, 2),
                    ]
                );
                $prevWater = $currWater;

                // Gas (flat)
                UtilityReading::updateOrCreate(
                    ['unit_id' => $unit->id, 'utility_type_id' => $gas->id, 'period_month' => $cursor->toDateString()],
                    ['previous_reading' => 0, 'current_reading' => 0, 'consumption' => 0, 'amount' => $gas->flat_fee]
                );

                // Service charge (flat)
                UtilityReading::updateOrCreate(
                    ['unit_id' => $unit->id, 'utility_type_id' => $service->id, 'period_month' => $cursor->toDateString()],
                    ['previous_reading' => 0, 'current_reading' => 0, 'consumption' => 0, 'amount' => $service->flat_fee]
                );

                // Build invoice
                $utilityTotal = UtilityReading::where('unit_id', $unit->id)
                    ->whereDate('period_month', $cursor->toDateString())
                    ->sum('amount');

                $subtotal = $lease->monthly_rent;
                $total = (string) ($subtotal + $utilityTotal);
                $invoiceNumber = 'INV-'.$cursor->format('Ym').'-'.str_pad((string)($lease->id * 100 + $cursor->month), 4, '0', STR_PAD_LEFT);

                $invoice = Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'lease_id' => $lease->id,
                    'period_month' => $cursor->toDateString(),
                    'issue_date' => $cursor->toDateString(),
                    'due_date' => $cursor->copy()->day(5)->toDateString(),
                    'subtotal' => (string) $subtotal,
                    'utility_total' => (string) $utilityTotal,
                    'late_fee' => '0',
                    'total' => $total,
                    'paid_amount' => '0',
                    'status' => 'unpaid',
                ]);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'type' => 'rent',
                    'description' => 'Monthly rent — '.$cursor->format('F Y'),
                    'quantity' => 1,
                    'unit_price' => (string) $subtotal,
                    'amount' => (string) $subtotal,
                ]);
                foreach (UtilityReading::where('unit_id', $unit->id)->whereDate('period_month', $cursor->toDateString())->with('utilityType')->get() as $r) {
                    $desc = $r->utilityType->name;
                    if ($r->utilityType->is_metered) {
                        $desc .= " ({$r->consumption} {$r->utilityType->unit_label})";
                    }
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'type' => 'utility',
                        'description' => $desc,
                        'quantity' => $r->utilityType->is_metered ? $r->consumption : 1,
                        'unit_price' => $r->utilityType->is_metered ? $r->utilityType->rate_per_unit : $r->amount,
                        'amount' => $r->amount,
                    ]);
                }

                // Payment simulation — older months paid, newer months mixed
                $monthsOld = (int) abs(Carbon::now()->startOfMonth()->diffInMonths($cursor));
                $methods = ['cash', 'bkash', 'nagad', 'bank'];
                $method = $methods[array_rand($methods)];

                if ($monthsOld >= 2) {
                    // Fully paid
                    Payment::create([
                        'invoice_id' => $invoice->id,
                        'amount' => $total,
                        'paid_on' => $cursor->copy()->day(random_int(2, 10))->toDateString(),
                        'method' => $method,
                        'reference' => $method === 'cash' ? null : strtoupper(substr(md5($invoice->id), 0, 10)),
                    ]);
                    $invoice->update(['paid_amount' => $total, 'status' => 'paid']);
                } elseif ($monthsOld === 1) {
                    // 70% paid fully, 20% partial, 10% unpaid
                    $r = random_int(1, 100);
                    if ($r <= 70) {
                        Payment::create([
                            'invoice_id' => $invoice->id, 'amount' => $total,
                            'paid_on' => $cursor->copy()->day(random_int(5, 25))->toDateString(),
                            'method' => $method,
                        ]);
                        $invoice->update(['paid_amount' => $total, 'status' => 'paid']);
                    } elseif ($r <= 90) {
                        $partial = (string) round($total * 0.6, 2);
                        Payment::create([
                            'invoice_id' => $invoice->id, 'amount' => $partial,
                            'paid_on' => $cursor->copy()->day(random_int(8, 20))->toDateString(),
                            'method' => $method,
                        ]);
                        $invoice->update(['paid_amount' => $partial, 'status' => 'partial']);
                    } else {
                        $invoice->update(['status' => 'overdue']);
                    }
                } else {
                    // Current month — 40% paid, 30% partial, 30% unpaid
                    $r = random_int(1, 100);
                    if ($r <= 40) {
                        Payment::create([
                            'invoice_id' => $invoice->id, 'amount' => $total,
                            'paid_on' => Carbon::now()->subDays(random_int(0, 10))->toDateString(),
                            'method' => $method,
                        ]);
                        $invoice->update(['paid_amount' => $total, 'status' => 'paid']);
                    } elseif ($r <= 70) {
                        $partial = (string) round($total * 0.5, 2);
                        Payment::create([
                            'invoice_id' => $invoice->id, 'amount' => $partial,
                            'paid_on' => Carbon::now()->subDays(random_int(0, 5))->toDateString(),
                            'method' => $method,
                        ]);
                        $invoice->update(['paid_amount' => $partial, 'status' => 'partial']);
                    }
                }

                $cursor->addMonth();
            }
        }

        // Maintenance requests — a handful
        $occupiedUnits = Unit::where('status', 'occupied')->with('activeLease')->take(5)->get();
        $issues = [
            ['Leaking kitchen tap', 'low'],
            ['AC not cooling',      'high'],
            ['Electrical socket sparking', 'urgent'],
            ['Bathroom door stuck', 'normal'],
            ['Window seal damaged', 'low'],
        ];
        foreach ($occupiedUnits as $i => $unit) {
            [$title, $priority] = $issues[$i];
            MaintenanceRequest::create([
                'unit_id' => $unit->id,
                'tenant_id' => $unit->activeLease?->tenant_id,
                'title' => $title,
                'description' => 'Reported by tenant during routine check.',
                'priority' => $priority,
                'status' => $i < 2 ? 'resolved' : ($i < 4 ? 'in_progress' : 'open'),
                'cost' => $i < 2 ? random_int(500, 3000) : 0,
                'reported_on' => Carbon::now()->subDays(random_int(1, 30))->toDateString(),
                'resolved_on' => $i < 2 ? Carbon::now()->subDays(random_int(0, 5))->toDateString() : null,
            ]);
        }
    }
}
