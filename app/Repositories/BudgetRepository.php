<?php

namespace App\Repositories;

use App\Models\Budget;
use App\Models\Spending;
use Exception;
use Illuminate\Support\Facades\DB;

class BudgetRepository
{
    public function getValidationRules()
    {
        return [
            'tag_id' => 'required|exists:budgets,id', // TODO CHECK IF TAG BELONGS TO USER
            'period' => 'required|in:' . implode(',', $this->getSupportedPeriods()),
            'amount' => 'required' // TODO NUMERIC
        ];
    }

    public function getSupportedPeriods(): array
    {
        return [
            'yearly',
            'monthly',
            'biweekly',
            'weekly',
            'daily'
        ];
    }

    public function doesExist(int $spaceId, int $tagId): bool
    {
        return DB::selectOne('
                SELECT COUNT(*) AS count
                FROM budgets
                WHERE
                    space_id = ?
                    AND tag_id = ?
                    AND (
                        starts_on <= NOW()
                        AND (
                            ends_on IS NULL
                            OR ends_on > NOW()
                        )
                    )
            ', [
                $spaceId,
                $tagId
            ])->count == 1;
    }

    public function getActive()
    {
        $today = date('Y-m-d');

        return Budget::whereRaw('starts_on <= ?', [$today])
            ->whereRaw('ends_on >= ? OR ends_on IS NULL', [$today])
            ->get();
    }

    public function getById(int $id): ?Budget
    {
        return Budget::find($id);
    }

    public function getSpentById(int $id): int
    {
        $budget = $this->getById($id);

        if (!$budget) {
            throw new Exception('Could not find budget (where ID is ' . $id . ')');
        }

        if ($budget->period === 'monthly') {
            return Spending::where('space_id', session('space')->id)
                ->where('tag_id', $budget->tag->id)
                ->whereRaw('MONTH(happened_on) = ?', [date('n')])
                ->whereRaw('YEAR(happened_on) = ?', [date('Y')])
                ->sum('amount');
        }

        throw new Exception('No clue what to do with period "' . $budget->period . '"');
    }

    public function create(int $spaceId, int $tagId, string $period, int $amount): void
    {
        if ($this->doesExist($spaceId, $tagId)) {
            throw new Exception(vsprintf('Budget (with space ID being %s and tag ID being %s) already exists', [
                $spaceId,
                $tagId
            ]));
        }

        if (!in_array($period, $this->getSupportedPeriods())) {
            throw new Exception('Unknown period "' . $period . '"');
        }

        Budget::create([
            'space_id' => $spaceId,
            'tag_id' => $tagId,
            'period' => $period,
            'amount' => $amount,
            'starts_on' => date('Y-m-d')
        ]);
    }
}
