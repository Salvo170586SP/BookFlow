<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberRequest;
use App\Http\Resources\LoanResource;
use App\Http\Resources\MemberResource;
use App\Models\Copy;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $members = User::query()
            ->role('member')
            ->withCount('loans')
            ->with('detail')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(12);

        return MemberResource::collection($members);
    }

    public function show(User $member): MemberResource
    {
        return new MemberResource($member->load(['detail', 'loans.copy.book']));
    }

    public function update(MemberRequest $request, User $member): MemberResource
    {
        $data = $request->validated();
        $detailData = Arr::except($data, ['name']);

        DB::transaction(function () use ($member, $data, $detailData): void {
            $member->update(Arr::only($data, ['name']));

            if ($detailData !== []) {
                $member->detail()->updateOrCreate([], $detailData);
            }
        });

        return new MemberResource($member->load('detail'));
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

    public function addLoan(Request $request, User $member): LoanResource
    {
        $user = $request->user();
        $isAdmin = $user?->hasRole('admin') ?? false;
        $isMemberBookingForSelf = $user?->is($member) && $member->hasRole('member');

        abort_unless($isAdmin || $isMemberBookingForSelf, 403);
        abort_unless($member->hasRole('member'), 404);

        $rules = [
            'due_at' => ['required', 'date', 'after:now'],
        ];

        if ($isAdmin) {
            $rules['copy_id'] = ['required', 'integer', 'exists:copies,id'];
            $rules['book_id'] = ['prohibited'];
        } else {
            $rules['copy_id'] = ['prohibited'];
            $rules['book_id'] = ['required', 'integer', 'exists:books,id'];
        }

        $data = $request->validate($rules);

        $loan = DB::transaction(function () use ($member, $data, $isAdmin): Loan {
            if ($isAdmin) {
                $copy = Copy::query()
                    ->lockForUpdate()
                    ->findOrFail($data['copy_id']);
            } else {
                $copy = Copy::query()
                    ->where('book_id', $data['book_id'])
                    ->where('status', 'available')
                    ->whereDoesntHave('loans', fn($query) => $query->whereNull('returned_at'))
                    ->inRandomOrder()
                    ->lockForUpdate()
                    ->first();

                if (!$copy) {
                    throw ValidationException::withMessages([
                        'book_id' => 'Non ci sono copie disponibili per questo libro.',
                    ]);
                }
            }

            if ($copy->status !== 'available' || $copy->activeLoan()->exists()) {
                $errorField = $isAdmin ? 'copy_id' : 'book_id';
                $errorMessage = $isAdmin
                    ? 'La copia selezionata non è disponibile per il prestito.'
                    : 'Non ci sono copie disponibili per questo libro.';

                throw ValidationException::withMessages([
                    $errorField => $errorMessage,
                ]);
            }

            return $member->loans()->create([
                'copy_id' => $copy->id,
                'loaned_at' => now(),
                'due_at' => $data['due_at'],
            ]);
        });

        return new LoanResource($loan->load('copy.book'));
    }
}
