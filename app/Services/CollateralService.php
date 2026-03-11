<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Borrower;
use App\Models\CollateralAsset;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CollateralService
{
    /**
     * Register a new collateral asset for a borrower.
     *
     * @param  Borrower  $borrower
     * @param  array     $data     Validated request data
     * @param  User      $by
     * @return CollateralAsset
     */
    public function register(Borrower $borrower, array $data, User $by): CollateralAsset
    {
        return DB::transaction(function () use ($borrower, $data, $by) {
            $asset = CollateralAsset::create([
                ...$data,
                'borrower_id' => $borrower->id,
                'status'      => 'available',
                'created_by'  => $by->id,
            ]);

            AuditLog::record('collateral.registered', $asset, [], $asset->toArray());

            return $asset;
        });
    }

    /**
     * Update an asset's valuation details.
     *
     * @param  CollateralAsset  $asset
     * @param  array            $data  Keys: estimated_value, valuation_date, valuer_name, valuation_firm
     * @param  User             $by
     * @return CollateralAsset
     */
    public function updateValuation(CollateralAsset $asset, array $data, User $by): CollateralAsset
    {
        $old = $asset->only(['estimated_value', 'valuation_date', 'valuer_name']);

        $asset->update($data);

        AuditLog::record('collateral.valuation_updated', $asset, $old, $asset->fresh()->only(array_keys($data)));

        return $asset->fresh();
    }

    /**
     * Pledge an asset to a loan.
     * Validates the asset is currently available (not already pledged).
     *
     * @throws ValidationException
     */
    public function pledge(CollateralAsset $asset): CollateralAsset
    {
        if ($asset->status === 'pledged') {
            throw ValidationException::withMessages([
                'asset' => "Asset {$asset->display_label} is already pledged to another loan.",
            ]);
        }

        $asset->update(['status' => 'pledged']);

        AuditLog::record('collateral.pledged', $asset, ['status' => 'available'], ['status' => 'pledged']);

        return $asset->fresh();
    }

    /**
     * Release a previously pledged asset back to available.
     * Called automatically by LoanService::close().
     */
    public function release(CollateralAsset $asset): CollateralAsset
    {
        $asset->update(['status' => 'released']);

        AuditLog::record('collateral.released', $asset, ['status' => 'pledged'], ['status' => 'released']);

        return $asset->fresh();
    }

    /**
     * Calculate the maximum loan amount allowed for a given asset,
     * based on the product's LTV limit.
     *
     * @param  CollateralAsset  $asset
     * @param  float            $maxLtvPercent  e.g. 80.0
     * @return float
     */
    public function maxLoanAmount(CollateralAsset $asset, float $maxLtvPercent): float
    {
        if (! $asset->estimated_value) {
            return 0.0;
        }

        return round((float) $asset->estimated_value * $maxLtvPercent / 100, 2);
    }
}
