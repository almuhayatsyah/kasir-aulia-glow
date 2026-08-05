<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Exports\TransactionsExport;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    // ─── Riwayat Transaksi ───────────────────────────────────────

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

    // ─── Laporan Penjualan ────────────────────────────────────────

    public function exportSalesReportExcel(Request $request): BinaryFileResponse
    {
        $dateFrom = $request->query('dari', now()->format('Y-m-d'));
        $dateTo   = $request->query('sampai', now()->format('Y-m-d'));

        $filename = 'laporan-penjualan-aulia-glow-' . $dateFrom . '-sd-' . $dateTo . '.xlsx';

        return Excel::download(
            new SalesReportExport($dateFrom, $dateTo),
            $filename
        );
    }

    public function exportSalesReportPdf(Request $request): Response
    {
        $dateFrom = $request->query('dari', now()->format('Y-m-d'));
        $dateTo   = $request->query('sampai', now()->format('Y-m-d'));

        // Summary
        $stats = Transaction::query()
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->selectRaw('COALESCE(SUM(total_amount), 0) as omzet')
            ->selectRaw('COALESCE(SUM(total_hpp), 0) as hpp')
            ->selectRaw('COALESCE(SUM(total_profit), 0) as profit')
            ->selectRaw('COUNT(*) as transaksi')
            ->first();

        $omzet     = (int) $stats?->omzet;
        $transaksi = (int) $stats?->transaksi;

        $summary = [
            'omzet'      => $omzet,
            'hpp'        => (int) $stats?->hpp,
            'profit'     => (int) $stats?->profit,
            'transaksi'  => $transaksi,
            'rata_rata'  => $transaksi > 0 ? (int) round($omzet / $transaksi) : 0,
        ];

        // Top Products
        $topProducts = TransactionDetail::query()
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereDate('transactions.created_at', '>=', $dateFrom)
            ->whereDate('transactions.created_at', '<=', $dateTo)
            ->selectRaw('products.name')
            ->selectRaw('SUM(transaction_details.qty) as qty')
            ->selectRaw('SUM(transaction_details.subtotal) as subtotal')
            ->groupBy('products.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'name'     => $item->name,
                'qty'      => (int) $item->qty,
                'subtotal' => (int) $item->subtotal,
            ])
            ->toArray();

        // Daily Breakdown
        $dailyBreakdown = Transaction::query()
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->selectRaw('DATE(created_at) as sale_date')
            ->selectRaw('SUM(total_amount) as omzet')
            ->selectRaw('SUM(total_profit) as profit')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('sale_date')
            ->orderBy('sale_date', 'desc')
            ->get()
            ->map(fn ($item) => [
                'date'   => \Carbon\Carbon::parse($item->sale_date)->format('d/m/Y'),
                'omzet'  => (int) $item->omzet,
                'profit' => (int) $item->profit,
                'count'  => (int) $item->count,
            ])
            ->toArray();

        $pdf = Pdf::loadView('exports.sales-report-pdf', [
            'summary'        => $summary,
            'topProducts'    => $topProducts,
            'dailyBreakdown' => $dailyBreakdown,
            'dateFrom'       => $dateFrom,
            'dateTo'         => $dateTo,
        ])->setPaper('a4', 'portrait');

        $filename = 'laporan-penjualan-aulia-glow-' . $dateFrom . '-sd-' . $dateTo . '.pdf';

        return $pdf->download($filename);
    }
}
