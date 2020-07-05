<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Repositories\BudgetRepository;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    private $budgetRepository;

    public function __construct(BudgetRepository $budgetRepository)
    {
        $this->budgetRepository = $budgetRepository;
    }

    public function index()
    {
        return view('budgets.index', [
            'budgets' => $this->budgetRepository->getActive()
        ]);
    }

    public function create()
    {
        return view('budgets.create', [
            'tags' => session('space')->tags()->orderBy('created_at', 'DESC')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->budgetRepository->getValidationRules());

        if ($this->budgetRepository->doesExist(session('space')->id, $request->tag_id)) {
            return redirect('/budgets/create')
                ->with('message', 'A budget like this already exists');
        }

        $amount = Helper::rawNumberToInteger($request->amount);
        $this->budgetRepository->create(session('space')->id, $request->tag_id, $request->period, $amount);

        return redirect('/budgets');
    }
}
