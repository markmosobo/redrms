<?php

namespace App\Services;

use App\Models\{
    Tenancy,
    Deposit,
    Inspection,
    User,
    Notification
};
use Illuminate\Support\Facades\DB;

class TenancyTerminationService
{
    public function terminate(Tenancy $tenancy, ?int $terminatedBy = null): void
    {
        // 🛡 Prevent double termination
        if ($tenancy->status === 'terminated') {
            return;
        }

        // 1️⃣ Terminate tenancy
        $tenancy->update([
            'status'   => 'terminated',
            'end_date' => now()
        ]);

        // 2️⃣ Free unit
        $tenancy->unit?->update([
            'status' => 'vacant'
        ]);

        // 3️⃣ Get active deposit
        $deposit = Deposit::where('tenancy_id', $tenancy->id)
            ->where('status', 'held')
            ->first();

        if (! $deposit) {
            return;
        }

        // 4️⃣ Mark deposit under inspection
        $deposit->update([
            'status' => 'under_inspection'
        ]);

        // 5️⃣ Create move-out inspection
        Inspection::firstOrCreate(
            [
                'tenancy_id'      => $tenancy->id,
                'inspection_type'=> 'move_out'
            ],
            [
                'unit_id'         => $tenancy->unit_id,
                'inspection_date' => null,
                'created_by'      => $terminatedBy,
                'status'          => 'draft',
                'notes'           => null
            ]
        );

        // 6️⃣ Notify managers
        $this->notifyManagers($tenancy);
    }

    protected function notifyManagers(Tenancy $tenancy): void
    {
        $managers = User::where('role', 'manager')->get();

        foreach ($managers as $manager) {
            Notification::create([
                'user_id'       => $manager->id,
                'title'         => 'Inspection Required',
                'message'       => 'Unit ' . $tenancy->unit->unit_number .
                                   ' is ready for move-out inspection.',
                'type'          => 'inspection_required',
                'resource_type' => 'tenancy',
                'resource_id'   => $tenancy->id,
            ]);
        }
    }
}