<?php

namespace App\Services;

/**
 * Nepal Tax Calculator Service
 * 
 * Calculates income tax based on Nepal's tax rules for FY 2080/81 (2023/24)
 * Tax brackets:
 * - First NPR 400,000: 1%
 * - Next NPR 100,000 (400,001 - 500,000): 10%
 * - Next NPR 200,000 (500,001 - 700,000): 20%
 * - Next NPR 1,300,000 (700,001 - 2,000,000): 30%
 * - Above NPR 2,000,000: 36%
 * 
 * Married individuals get additional tax benefits (reduced rates or exemptions)
 */
class NepalTaxCalculator
{
    /**
     * Calculate annual tax based on annual income and marital status
     * 
     * @param float $annualIncome Annual income in NPR
     * @param string $maritalStatus 'single' or 'married'
     * @return float Annual tax amount
     */
    public function calculateAnnualTax(float $annualIncome, string $maritalStatus = 'single'): float
    {
        // Tax exemption threshold (varies by marital status)
        $exemptionThreshold = $maritalStatus === 'married' ? 500000 : 400000;
        
        // If income is below exemption threshold, no tax
        if ($annualIncome <= $exemptionThreshold) {
            return 0;
        }
        
        // Calculate taxable income (after exemption)
        $taxableIncome = $annualIncome - $exemptionThreshold;
        
        // Tax brackets (for taxable income after exemption)
        $tax = 0;
        $remainingIncome = $taxableIncome;
        
        // First bracket: 0 - 100,000 (after exemption) = 1%
        if ($remainingIncome > 0) {
            $bracketAmount = min($remainingIncome, 100000);
            $tax += $bracketAmount * 0.01;
            $remainingIncome -= $bracketAmount;
        }
        
        // Second bracket: 100,001 - 200,000 = 10%
        if ($remainingIncome > 0) {
            $bracketAmount = min($remainingIncome, 100000);
            $tax += $bracketAmount * 0.10;
            $remainingIncome -= $bracketAmount;
        }
        
        // Third bracket: 200,001 - 1,500,000 = 20%
        if ($remainingIncome > 0) {
            $bracketAmount = min($remainingIncome, 1300000);
            $tax += $bracketAmount * 0.20;
            $remainingIncome -= $bracketAmount;
        }
        
        // Fourth bracket: 1,500,001 - 2,000,000 = 30%
        if ($remainingIncome > 0) {
            $bracketAmount = min($remainingIncome, 500000);
            $tax += $bracketAmount * 0.30;
            $remainingIncome -= $bracketAmount;
        }
        
        // Fifth bracket: Above 2,000,000 = 36%
        if ($remainingIncome > 0) {
            $tax += $remainingIncome * 0.36;
        }
        
        return round($tax, 2);
    }
    
    /**
     * Calculate monthly tax from monthly salary
     * 
     * @param float $monthlySalary Monthly salary in NPR
     * @param string $maritalStatus 'single' or 'married'
     * @return float Monthly tax amount
     */
    public function calculateMonthlyTax(float $monthlySalary, string $maritalStatus = 'single'): float
    {
        $annualIncome = $monthlySalary * 12;
        $annualTax = $this->calculateAnnualTax($annualIncome, $maritalStatus);
        $monthlyTax = $annualTax / 12;
        
        return round($monthlyTax, 2);
    }
    
    /**
     * Calculate tax for a specific payment period
     * 
     * @param float $baseAmount Base salary amount
     * @param float $commissionAmount Commission amount (optional)
     * @param float $bonusAmount Bonus amount (optional)
     * @param string $maritalStatus 'single' or 'married'
     * @return float Tax amount for the period
     */
    public function calculateTaxForPeriod(
        float $baseAmount,
        float $commissionAmount = 0,
        float $bonusAmount = 0,
        string $maritalStatus = 'single'
    ): float {
        // Total income for the period
        $periodIncome = $baseAmount + $commissionAmount + $bonusAmount;
        
        // Calculate annual equivalent (assuming monthly payment)
        $annualIncome = $periodIncome * 12;
        
        // Calculate annual tax
        $annualTax = $this->calculateAnnualTax($annualIncome, $maritalStatus);
        
        // Monthly tax
        $monthlyTax = $annualTax / 12;
        
        return round($monthlyTax, 2);
    }
}

