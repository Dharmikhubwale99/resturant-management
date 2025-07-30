<?php

namespace App\Livewire\Resturant\Report;

use App\Models\Expense;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExpenseExport;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Exp;

class ExpenseReport extends Component
{
    use WithPagination;

    public $fromDate;
    public $toDate;
    public $filterType = 'today';
    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.report.expense-report', [
            'expenses' => $this->expenses,
        ]);
    }

    public function mount()
    {
        $this->setDefaultDates();
    }

    public function setDefaultDates()
    {
        $today = now()->format('Y-m-d');
        $this->fromDate = $today;
        $this->toDate = $today;
    }

    public function updatedFilterType()
    {
        $this->resetPage();
        switch ($this->filterType) {
            case 'weekly':
                $this->fromDate = now()->startOfWeek()->format('Y-m-d');
                $this->toDate = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $this->fromDate = now()->startOfMonth()->format('Y-m-d');
                $this->toDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'custom':
                // Keep manual input active
                break;
            default:
                $this->setDefaultDates();
                break;
        }
    }

    public function updatedFromDate()
    {
        $this->resetPage();
    }

    public function updatedToDate()
    {
        $this->resetPage();
    }

    public function getExpensesProperty()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        return Expense::where('restaurant_id', $restaurantId)
            ->whereBetween('paid_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->latest()
            ->paginate(10);
    }

    public function getTotalAmountProperty()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        return Expense::where('restaurant_id', $restaurantId)
            ->whereBetween('paid_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->sum('amount');
    }

    public function exportExcel()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;
        return Excel::download(
            new ExpenseExport($this->fromDate, $this->toDate, $restaurantId),
            'expense_report.xlsx'
        );
    }

    public function exportPdf()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        $expenses = Expense::with('expenseType')
            ->where('restaurant_id', $restaurantId)
            ->whereBetween('paid_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->get();

        $totalAmount = $expenses->sum('amount'); // Fix this line

        $pdf = Pdf::loadView('livewire.pdf.expense-report-pdf', [
            'expenses' => $expenses,
            'totalAmount' => $totalAmount,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'expense_report.pdf');
    }
}
