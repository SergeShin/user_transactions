<?php

namespace App\Repository;

use App\Traits\Repository\Paginate;
use App\Transaction;
use App\User;

class UserRepository
{
    use Paginate;

    protected $availableSorColumns = ['created_at', 'name', 'email'];
    protected $sortColumn = "-created_at";

    protected $availableLimits = [10, 25, 100];

    protected $limit = 10;

    public function fetchItems(?int $limit, ?string $sort)
    {
        $query = User::query();

        $query->select("users.*")
            ->where("permissions", User::PERMISSIONS_USER);

        $amountSum = Transaction::selectRaw('sum(amount)')
            ->whereColumn('user_id', 'users.id')
            ->where('type', Transaction::TYPE_DEBIT)
            ->getQuery();

        $query->selectSub($amountSum, 'debit_sum');

        return $this->paginate($query, $limit, $sort);
    }

    public function fetchItem(int $userId)
    {
        $query = User::query();

        $query->select("users.*")
            ->where("permissions", User::PERMISSIONS_USER);

        $amountSum = Transaction::selectRaw('sum(amount)')
            ->whereColumn('user_id', 'users.id')
            ->where('type', Transaction::TYPE_DEBIT)
            ->getQuery();

        $query->selectSub($amountSum, 'debit_sum');

        return $query->find($userId);
    }
}
