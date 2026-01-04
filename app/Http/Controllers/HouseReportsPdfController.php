<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\HousesupportReports;

class HouseReportsPdfController extends Controller
{
    public function download(Request $request)
    {
        $date = $request->query('date');

        if (!$date) {
            return redirect()->back()->with('error', 'Please select a date first.');
        }

        $date = Carbon::parse($date);

        // Use your existing HousesupportReports methods for calculations
        $reportComponent = new HousesupportReports();
        $reportComponent->searchDate = $date->toDateString();

        $pdfData = $reportComponent->generatePdfData(); // We'll create this method next

        $pdf = Pdf::loadView('pdf.housesupport-reports', compact('pdfData'));

        return $pdf->download('housesupport-report-' . $date->format('Y-m-d') . '.pdf');
    }
}
