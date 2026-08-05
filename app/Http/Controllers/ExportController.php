<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $dateFrom = $request->query('dari', '');
        $dateTo   = $request->query('sampai', '');
        $search   = $request->query('q', '');

        $filename = 'transaksi-aulia-glow';
        if ($dateFrom) {
            $filename .= '-dari-' . $dateFrom;
        }
        if ($dateTo) {
            $filename .= '-sampai-' . $dateTo;
        }
        $filename .= '.xlsx';

        return Excel::download(
            new TransactionsExport($dateFrom, $dateTo, $search),
            $filename
        );
    }

    public function exportPdf(Request $request): Response
    {
        $dateFrom = $request->query('dari', '');
        $dateTo   = $request->query('sampai', '');
        $search   = $request->query('q', '');

        $transactions = Transaction::query()
            ->with(['details.product'])
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($search, function ($q) use ($search): void {
                $q->where(function ($sub) use ($search): void {
                    $sub->where('id', 'like', '%' . $search . '%')
                        ->orWhereHas('details.product', fn ($p) => $p->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->latest()
            ->get();

        $pdf = Pdf::loadView('exports.transactions-pdf', [
            'transactions' => $transactions,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
        ])->setPaper('a4', 'landscape');

        $filename = 'transaksi-aulia-glow';
        if ($dateFrom) {
            $filename .= '-dari-' . $dateFrom;
        }
        if ($dateTo) {
            $filename .= '-sampai-' . $dateTo;
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }
}
