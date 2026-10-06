<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\User;

class LoanController extends Controller
{
    public function destroyForMember(User $member, Loan $loan): void
    {
        $member->loans()->findOrFail($loan->getKey())->delete();
    }
}
