<?php

namespace App\Services;

use App\Models\LoyaltyCard;
use App\Models\Purchased;
use App\Models\Reward;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Support\SettingsCache; // <-- plural, correct one

class LoyaltyCardSV extends BaseService
{
    // ---- Configurable rules ----
    private const MAX_SLOTS         = 11;
    private const FIRST_REWARD_SLOT = 5;

    protected ?string $modelLabel = 'Loyalty Card';

    protected function getQuery()
    {
        return LoyaltyCard::query();
    }

    public function getAllLoyaltyCards($active = null)
    {
        // If $active is null, get all (active and inactive), except deleted (handled by default scopes)
        if ($active !== null) {
            $active = ($active == 1) ? 1 : 0; // Ensure only 0 or 1
            return $this->getQuery()->where('active', $active)->get();
        }
        return $this->getAll();
    }

    public function getLoyaltyCard($global_id)
    {
        return $this->getByGlobalId(LoyaltyCard::class, $global_id);
    }

    /** Public API: attach a purchase to the user’s active (non-expired) card. */
    public function attachPurchase(Purchased $purchase, ?int $ttlDays = null): LoyaltyCard
    {
        return DB::transaction(function () use ($purchase, $ttlDays) {
            $userId = (int) $purchase->user_id;

            // 1) guard: never record the same purchase twice
            if ($this->isAlreadyRecorded($userId, $purchase->id)) {
                return $this->activeCard($userId) ?? $this->createNewCard($userId, $ttlDays);
            }

            // 2) get (or create) a valid active card, respecting expiry
            $card = $this->activeCardForUpdate($userId);

            if (!$card || $this->isExpired($card)) {
                if ($card) $this->activate($card->global_id, false); // via BaseService
                $card = $this->createNewCard($userId, $ttlDays);
            }

            // 3) find first empty slot; if full, rotate to a new card
            $slotCol = $this->firstEmptySlot($card);
            if (!$slotCol) {
                $this->activate($card->global_id, false);
                $card   = $this->createNewCard($userId, $ttlDays);
                $slotCol = 'purchase1_id';
            }

            // 4) place the purchase, update points
            $card->$slotCol = $purchase->id;
            $card->points   = (int) $card->points + 1;
            $card->save();

            $slotNumber = $this->slotNumber($slotCol);

            // 5) reward signals (5th)
            // if ($slotNumber === self::FIRST_REWARD_SLOT) {
            //     // nothing to write now; you can expose canClaimFirstReward($card) if needed
            // }

            // 6) 11th -> service free (extras normal), close card, open next
            if ($slotNumber === self::MAX_SLOTS) {
                $this->makeServiceGiftOnly($purchase);
                // user_id, services_id, admin_id, branch_id are already set in $purchase
                // create reward    
                $Reward = Reward::create([
                    'user_id' => $userId,
                    'service_id' => $purchase->service_id,
                    'reward_type' => 'second',
                    'admin_id' => auth()->user()->id,
                    'branch_id' => auth()->user()->branch_id,
                ]);
                $reward = $Reward->refresh();
                $card->second_reward_id = $reward->id;
                $card->save();
                // $this->activate($card->global_id, false);
                $this->createNewCard($userId, $ttlDays);
            }

            return $card->fresh();
        });
    }

    /* ---------------- helpers (kept DRY + fast) ---------------- */

    /** Lock the current active card row for this user, if any. */
    private function activeCardForUpdate(int $userId): ?LoyaltyCard
    {
        return $this->getQuery()
            ->where('user_id', $userId)
            ->where('active', 1)
            ->lockForUpdate()
            ->latest('id')
            ->first();
    }

    /** Read-only lookup (no lock). */
    private function activeCard(int $userId): ?LoyaltyCard
    {
        return $this->getQuery()
            ->where('user_id', $userId)
            ->where('active', 1)
            ->latest('id')
            ->first();
    }

    private function isExpired(LoyaltyCard $card): bool
    {
        // Ensure: protected $casts = ['expires_at' => 'datetime']; on the model
        return $card->expires_at instanceof Carbon && $card->expires_at->isPast();
    }

    private function createNewCard(int $userId, ?int $ttlDays = null): LoyaltyCard
    {
        $expired = (int) SettingsCache::get('loyalty_card_expired');
        $expiresAt = now()->addDays($ttlDays ?? $expired ?? 30);

        // Use BaseService::create()
        return $this->create([
            'global_id'  => (string) Str::uuid(),
            'user_id'    => $userId,
            'points'     => 0,
            'active'     => 1,
            'expires_at' => $expiresAt,
        ]);
    }

    /** First NULL among purchase1_id..purchase11_id */
    private function firstEmptySlot(LoyaltyCard $card): ?string
    {
        for ($i = 1; $i <= self::MAX_SLOTS; $i++) {
            $col = "purchase{$i}_id";
            if (is_null($card->$col)) return $col;
        }
        return null;
    }

    private function slotNumber(string $col): int
    {
        return (int) filter_var($col, FILTER_SANITIZE_NUMBER_INT);
    }

    /** Fast duplicate check (single query with ORs). */
    private function isAlreadyRecorded(int $userId, int $purchaseId): bool
    {
        return $this->getQuery()
            ->where('user_id', $userId)
            ->where(function ($q) use ($purchaseId) {
                for ($i = 1; $i <= self::MAX_SLOTS; $i++) {
                    $q->orWhere("purchase{$i}_id", $purchaseId);
                }
            })
            ->exists();
    }

    /**
     * Make service free, extras normal (det/sft/acn).
     * Only touches fields that must change; no duplication of your pricing logic elsewhere.
     */
    private function makeServiceGiftOnly(Purchased $purchase): void
    {
        $purchase->is_gift = 1;

        // Service is free:
        $purchase->service_price = 0;

        // Extras charged as usual:
        $det = (int) ($purchase->det ?? 0);
        $sft = (int) ($purchase->sft ?? 0);
        $acn = (int) ($purchase->acn ?? 0);

        $detPrice = (int) ($purchase->det_price ?? 0);
        $sftPrice = (int) ($purchase->sft_price ?? 0);
        $acnPrice = (int) ($purchase->acn_price ?? 0);

        $purchase->total_price = ($det * $detPrice) + ($sft * $sftPrice) + ($acn * $acnPrice);
        $purchase->save();
    }
}
