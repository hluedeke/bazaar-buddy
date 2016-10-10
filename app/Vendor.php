<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;


class Vendor extends Model
{

    protected $fillable = [
        'name',
        'payment'
    ];


    public function bazaars()
    {
        return $this->belongsToMany('\App\Bazaar')->withPivot(
            'vendor_number',
            'checkout',
            'table_fee',
            'audit_adjust'
        )->withTimestamps();
    }

    public function salesSheets()
    {
        return $this->hasMany('\App\SalesSheet', 'vendor_id');
    }

    public function newPivot(Model $parent, array $attributes, $table, $exists)
    {
        return new \App\BazaarVendor($parent, $attributes, $table, $exists);
    }

    // Total hack, but couldn't find a better way to do it
    // Accesses the vendor number from the pivot table
    public function vendorNumber($bazaar)
    {
        $vendor_id = $this->id;
        return $bazaar->vendors()->newPivotStatementForId($vendor_id)
            ->first()->vendor_number;
    }

    public function checkout($bazaar)
    {
        return $bazaar->vendors()->newPivotStatementForId($this->id)
            ->first()->checkout;
    }

    public function tableFee($bazaar)
    {
        return $bazaar->vendors()->newPivotStatementForId($this->id)
            ->first()->table_fee;
    }

    public function auditAdjust($bazaar)
    {
        return $bazaar->vendors()->newPivotStatementForId($this->id)
            ->first()->audit_adjust;
    }

    public function totalCashSales($bazaarId, $date = null)
    {
        return $this->calculateSales($bazaarId, SalesType::CASH, $date);
    }

    public function totalCardSales($bazaarId, $date = null)
    {
        return $this->calculateSales($bazaarId, SalesType::CARD, $date);
    }

    public function totalLayawaySales($bazaarId, $date = null)
    {
        return $this->calculateSales($bazaarId, SalesType::LAYAWAY, $date);
    }

    public function totalSales($bazaarId, $date = null)
    {
        return $this->calculateSales($bazaarId, $date);
    }

    private function calculateSales($bazaarId, $type, $date)
    {
        $query = DB::table('vendors')->where('vendors.id', '=', $this->id)
            ->join('sales_sheets', 'sales_sheets.vendor_id', '=', 'vendors.id')
            ->where('sales_sheets.bazaar_id', '=', $bazaarId);
        if ($date)
            $query->whereBetween('sales_sheets.date_of_sales',
                [$date->format('Y-m-d').' 00:00:00', $date->format('Y-m-d').' 23:59:59']);
        $query->join('sales', 'sales.sales_sheet_id', '=', 'sales_sheets.id');
        if ($type)
            $query->where('sales.sales_type', '=', $type);
        return $query->sum('amount');

    }

    /**
     * Returns whether all of the associated sales sheets for this vendor have
     * been completely validated or not
     *
     * @return bool
     */
    public function isValidated($date = null)
    {
        foreach ($this->salesSheets as $sheet) {
            if ((($date != null && $sheet->date_of_sales->isSameDay($date)) || $date === null) &&
                $sheet->getValidationStatus()
                != Validated::CORRECT
            ) {
                return false;
            }
        }
        return true;
    }
}
