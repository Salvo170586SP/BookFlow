<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MemberController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $members = User::query()
            ->role('member')
            ->withCount('loans')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(12);

        return MemberResource::collection($members);
    }

    public function show(User $member): MemberResource
    {
        return new MemberResource($member->load(['loans.copy.book']));
    }

    public function update(MemberRequest $request,  User $member): MemberResource
    {
        $data = $request->validated();
        $member->update($data);

        return new MemberResource($member);
    }

    public function destroy(User $member): JsonResponse
    {
        $member->delete();

        return response()->json([
            'message' => 'Membro disiscritto con successo.'
        ]);
    }

    public function destroyLoan(User $member, Loan $loan, LoanController $loanController): JsonResponse
    {
        $loanController->destroyForMember($member, $loan);

        return response()->json([
            'message' => 'Prestito eliminato con successo.',
        ]);
    }
}
